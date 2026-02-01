<?php
// api/activate.php
require_once 'config.php';

$token = $_GET['token'] ?? '';
$message = '';
$redirect = false;

if ($token) {
    try {
        $pdo = getDB();
        
        // Check Token
        $stmt = $pdo->prepare("SELECT * FROM user_verifications WHERE token = ? AND type = 'activation' AND expires_at > NOW()");
        $stmt->execute([$token]);
        $verify = $stmt->fetch();

        if ($verify) {
            // Activate User
            $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?")->execute([$verify['user_id']]);
            
            // Delete Token
            $pdo->prepare("DELETE FROM user_verifications WHERE id = ?")->execute([$verify['id']]);
            
            $message = "Account Verified Successfully! You are redirecting to login...";
            $redirect = true;
        } else {
            $message = "Invalid or Expired Link.";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
} else {
    $message = "No token provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Activation</title>
    <style>
        body { background: #000; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif; text-align: center; }
        .box { padding: 40px; border: 1px solid #333; border-radius: 10px; background: #111; }
        h1 { margin-bottom: 20px; }
    </style>
    <?php if ($redirect): ?>
        <meta http-equiv="refresh" content="3;url=../index.php?page=login">
    <?php endif; ?>
</head>
<body>
    <div class="box">
        <h1><?php echo $message; ?></h1>
        <?php if ($redirect): ?>
            <p>Redirecting...</p>
        <?php else: ?>
            <a href="../index.php?page=login" style="color: white;">Go to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>
