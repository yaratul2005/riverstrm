<?php
// user/dashboard.php

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>

<div class="section" style="padding-top: 100px;">
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?></h1>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <p>Member since: <?php echo substr($user['created_at'], 0, 10); ?></p>
        
        <hr style="border-color: #333; margin: 30px 0;">
        
        <!-- Placeholder for Favorites/Watch History -->
        <div class="section-title">My Favorites</div>
        <p style="color: #888;">No favorites added yet.</p>
        
        <br>
        <a href="api/logout.php" class="btn">Logout</a>
    </div>
</div>
