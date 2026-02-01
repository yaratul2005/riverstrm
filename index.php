<?php
// index.php
require_once 'api/config.php';

// Initialize DB to ensure tables exist
try {
    $pdo = getDB();
} catch (Exception $e) {
    // If DB fails, assume not installed or config error
    header('Location: install/index.php');
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
</head>
<body>
    <?php if ($page !== 'watch'): ?>
    <header id="main-header">
        <div class="logo">Great10</div>
        <nav class="nav-links">
            <a href="index.php?page=home">Home</a>
            <a href="index.php?page=movies">Movies</a>
            <a href="index.php?page=series">Series</a>
            <a href="index.php?page=search">Search</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php?page=dashboard">Dashboard</a>
                <a href="api/logout.php" class="btn">Logout</a>
            <?php else: ?>
                <a href="index.php?page=login" class="btn">Login</a>
            <?php endif; ?>
        </nav>
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
                echo "<div class='section'><h1>404 - Page Not Found</h1></div>";
        }
        ?>
    </main>

    <script>
        // Scroll effect for header
        window.addEventListener('scroll', () => {
            const header = document.getElementById('main-header');
            if (header) {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            }
        });
    </script>
</body>
</html>
