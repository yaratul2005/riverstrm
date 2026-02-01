<?php
// admin/login.php
require_once '../api/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'admin';
        header('Location: index.php'); // Redirect to dashboard
        exit;
    } else {
        $error = "Invalid admin credentials.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <form method="POST" class="auth-form">
            <h2 style="text-align: center; margin-bottom: 20px;">Admin Access</h2>
            
            <?php if ($error): ?>
                <div style="color: red; margin-bottom: 10px; text-align: center;"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="form-group">
                <input type="email" name="email" placeholder="Admin Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn" style="width: 100%;">Login</button>
        </form>
    </div>
</body>
</html>
