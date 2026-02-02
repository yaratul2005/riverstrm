<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    // session_start(); // Handled in config.php usually
}
$pdo = getDB();
$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$siteName = $settings['site_name'] ?? 'Great10';
$logo = $settings['site_logo'] ?? '';
$favicon = $settings['site_favicon'] ?? '';
$headCode = $settings['head_code'] ?? '';
$page = $_GET['page'] ?? 'home'; // for active state
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $siteName; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if($favicon): ?><link rel="icon" href="<?php echo $favicon; ?>"><?php endif; ?>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <!-- Custom Head -->
    <?php echo $headCode; ?>
</head>
<body>
    <!-- 
    <div id="preloader">
        <div class="loader-logo">Great10</div>
    </div>
    -->

    <!-- Global Header -->
    <?php if ($page !== 'login' && strpos($_SERVER['REQUEST_URI'], 'login.php') === false): ?>
    <header id="main-header">
        <a href="index.php" class="logo">
            <?php if ($logo): ?>
                <img src="<?php echo $logo; ?>" alt="<?php echo $siteName; ?>" style="height: 35px; vertical-align: middle;">
            <?php else: ?>
                Great10
            <?php endif; ?>
        </a>
        <nav class="nav-links">
            <a href="index.php?page=home" class="<?php echo $page == 'home' ? 'active' : ''; ?>">Home</a>
            <a href="index.php?page=search" class="<?php echo $page == 'search' ? 'active' : ''; ?>">Search</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php?page=dashboard" class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">My List</a>
                <a href="api/logout.php">Logout</a>
            <?php else: ?>
                <a href="index.php?page=login" class="btn btn-primary" style="padding: 5px 15px; font-size: 0.8rem;">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <?php endif; ?>
