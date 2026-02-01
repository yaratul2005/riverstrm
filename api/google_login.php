<?php
// api/google_login.php
session_start();
require_once 'config.php';

$pdo = getDB();
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

$client_id = $settings['google_client_id'] ?? '';
$client_secret = $settings['google_client_secret'] ?? '';
$redirect_uri = $settings['google_redirect_uri'] ?? '';

if (!$client_id || !$client_secret) {
    die("Google Login not configured in Admin Panel.");
}

// 1. Redirect to Google
if (!isset($_GET['code'])) {
    $params = [
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'online'
    ];
    header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
    exit;
}

// 2. Handle Callback
if (isset($_GET['code'])) {
    try {
        // Exchange code for token
        $token_url = 'https://oauth2.googleapis.com/token';
        $post_data = [
            'code' => $_GET['code'],
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        ];
        
        $ch = curl_init($token_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!isset($response['access_token'])) {
            throw new Exception("Failed to get access token");
        }

        // Get User Info
        $user_info_url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $response['access_token'];
        $ch = curl_init($user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $user_info = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $google_id = $user_info['id'];
        $email = $user_info['email'];
        $name = $user_info['name'];
        $avatar = $user_info['picture'];

        // Login or Register
        $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
        $stmt->execute([$google_id, $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Update existing user
            if (!$user['google_id']) {
                $pdo->prepare("UPDATE users SET google_id = ?, avatar = ?, is_verified = 1 WHERE id = ?")->execute([$google_id, $avatar, $user['id']]);
            }
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
        } else {
            // Create new user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, google_id, avatar, is_verified) VALUES (?, ?, 'google_oauth', ?, ?, 1)");
            $stmt->execute([$name, $email, $google_id, $avatar]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $name;
            $_SESSION['role'] = 'user';
        }

        header('Location: ../index.php?page=dashboard');
        exit;

    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
