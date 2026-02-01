<?php
// api/auth.php
require_once 'config.php';
require_once 'smtp.php';

header('Content-Type: application/json');
$pdo = getDB();

$action = $_POST['action'] ?? '';

if ($action === 'register') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        exit;
    }

    try {
        // Check Email
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'error' => 'Email already registered.']);
            exit;
        }

        // Create User (Unverified)
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_verified) VALUES (?, ?, ?, 0)");
        $stmt->execute([$username, $email, $hash]);
        $userId = $pdo->lastInsertId();

        // Create Verification Token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $pdo->prepare("INSERT INTO user_verifications (user_id, token, expires_at) VALUES (?, ?, ?)")
            ->execute([$userId, $token, $expiry]);

        // Send Email
        $link = SITE_URL . "/api/activate.php?token=$token";
        $subject = "Activate your Account - " . SITE_NAME;
        $body = "
            <h2>Welcome to " . SITE_NAME . "!</h2>
            <p>Please click the link below to verify your email and activate your account:</p>
            <p><a href='$link'>$link</a></p>
            <p>This link expires in 24 hours.</p>
        ";

        $smtp = new SMTPService();
        if ($smtp->send($email, $subject, $body)) {
            echo json_encode(['success' => true, 'message' => 'Registration successful! Please check your email to activate.']);
        } else {
            // Fallback for dev: Auto-verify if SMTP fails
            $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?")->execute([$userId]);
            echo json_encode(['success' => true, 'message' => 'Registration successful! (SMTP Failed - Account Auto-Activated for Testing)']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

} elseif ($action === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] !== 'admin' && $user['is_verified'] == 0) {
                echo json_encode(['success' => false, 'error' => 'Please verify your email first.']);
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid credentials.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
