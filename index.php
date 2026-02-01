<?php
// index.php
require_once 'api/config.php';

// Initialize DB (Auto-Migration)
$pdo = getDB();

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap" rel="stylesheet">
</head>
<body>
    <div id="preloader">
        <div class="loader-logo">Great10</div>
    </div>

    <!-- Global Header (Always Visible) -->
    <?php if ($page !== 'login'): ?>
    <header id="main-header">
        <a href="index.php" class="logo">Great10</a>
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
        
        <!-- Mobile Toggle (Hidden in V2, handled by bottom nav) -->
    </header>
    <?php endif; ?>

    <main>
        <?php
        switch ($page) {
            case 'home':
                include 'home.php';
                break;
            case 'watch':
                include 'watch.php';
                break;
            case 'search':
                include 'search.php';
                break;
            case 'login':
                include 'login.php';
                break;
            case 'dashboard':
                include 'user/dashboard.php';
                break;
            default:
                echo "<div class='section' style='padding-top: 100px;'><h1>404 - Page Not Found</h1></div>";
        }
        ?>
    </main>

    <!-- Bottom Nav (Mobile Only) -->
    <nav class="bottom-nav">
        <a href="index.php?page=home" class="nav-item <?php echo $page == 'home' ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            <span>Home</span>
        </a>
        <a href="index.php?page=search" class="nav-item <?php echo $page == 'search' ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
            <span>Search</span>
        </a>
        <a href="index.php?page=dashboard" class="nav-item <?php echo $page == 'dashboard' ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            <span>Profile</span>
        </a>
    </nav>

    <script src="assets/js/main.js"></script>
</body>
</html>
