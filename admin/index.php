<?php
// admin/index.php
require_once '../api/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$pdo = getDB();
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$movieCount = $pdo->query("SELECT COUNT(*) FROM content WHERE type='movie'")->fetchColumn();
$seriesCount = $pdo->query("SELECT COUNT(*) FROM content WHERE type='series'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; height: 100vh; }
        .sidebar { width: 250px; background: #111; padding: 20px; border-right: 1px solid #333; }
        .sidebar a { display: block; padding: 10px; color: #888; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { color: white; background: #222; }
        .admin-content { flex: 1; padding: 40px; overflow-y: auto; }
        .stat-card { background: #111; padding: 20px; border: 1px solid #333; text-align: center; }
        .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <div class="sidebar">
            <h2 class="logo" style="margin-bottom: 30px;">Admin</h2>
            <a href="index.php" class="active">Dashboard</a>
            <a href="movies.php">Manage Movies</a>
            <a href="series.php">Manage Series</a>
            <a href="../index.php">View Site</a>
            <a href="../api/logout.php" style="margin-top: 50px; color: red;">Logout</a>
        </div>
        <div class="admin-content">
            <h1>Dashboard Overview</h1>
            <div class="stat-grid">
                <div class="stat-card">
                    <h3>Users</h3>
                    <p style="font-size: 2rem;"><?php echo $userCount; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Movies</h3>
                    <p style="font-size: 2rem;"><?php echo $movieCount; ?></p>
                </div>
                <!-- 
                <div class="stat-card">
                    <h3>Series</h3>
                    <p style="font-size: 2rem;"><?php echo $seriesCount; ?></p>
                </div> 
                -->
            </div>
        </div>
    </div>
</body>
</html>
