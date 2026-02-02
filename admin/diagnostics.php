<?php
// admin/diagnostics.php
// session_start();
require_once '../api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$pdo = getDB();

// 1. PHP Version
$php_version = phpversion();
$php_status = version_compare($php_version, '7.4', '>=') ? 'OK' : 'Warning (Upgrade Recommended)';

// 2. Database Connection
try {
    $pdo->query("SELECT 1");
    $db_status = "Connected";
    $db_color = "green";
} catch (Exception $e) {
    $db_status = "Failed: " . $e->getMessage();
    $db_color = "red";
}

// 3. Writable Directories
$dirs = ['../brain', '../install']; // Example dirs
$dir_checks = [];
foreach ($dirs as $dir) {
    if (file_exists($dir)) {
        $dir_checks[$dir] = is_writable($dir) ? 'Writable' : 'Not Writable';
    }
}

// 4. TMDB Connectivity
function checkTMDB() {
    $url = "https://api.themoviedb.org/3/configuration?api_key=" . TMDB_API_KEY;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($http_code === 200) ? "Online" : "Unreachable (HTTP $http_code)";
}
$tmdb_status = checkTMDB();

// 5. SMTP Check (Settings)
$smtp_config = $pdo->query("SELECT * FROM settings WHERE setting_key LIKE 'smtp_%'")->fetchAll(PDO::FETCH_KEY_PAIR);
$smtp_status = (!empty($smtp_config['smtp_host']) && !empty($smtp_config['smtp_user'])) ? "Configured" : "Not Configured";

?>
<!DOCTYPE html>
<html>
<head>
    <title>System Diagnostics</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .diag-box { background: #222; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .status-ok { color: #4caf50; font-weight: bold; }
        .status-err { color: #f44336; font-weight: bold; }
        .status-warn { color: #ff9800; font-weight: bold; }
    </style>
</head>
<body>
    <div style="max-width: 800px; margin: 50px auto; padding: 0 20px;">
        <h1 style="border-bottom: 1px solid #333; padding-bottom: 20px;">System Diagnostics</h1>
        <a href="index.php" style="color: #ccc; display: block; margin-bottom: 30px;">&larr; Back to Dashboard</a>

        <div class="diag-box">
            <h3>Server Environment</h3>
            <p>PHP Version: <span class="<?php echo ($php_status === 'OK') ? 'status-ok' : 'status-warn'; ?>"><?php echo $php_version; ?></span></p>
            <p>Database: <span style="color: <?php echo $db_color; ?>"><?php echo $db_status; ?></span></p>
        </div>

        <div class="diag-box">
            <h3>External APIs</h3>
            <p>TMDB API: <span class="<?php echo ($tmdb_status === 'Online') ? 'status-ok' : 'status-err'; ?>"><?php echo $tmdb_status; ?></span></p>
            <p>SMTP Email: <span class="<?php echo ($smtp_status === 'Configured') ? 'status-ok' : 'status-warn'; ?>"><?php echo $smtp_status; ?></span></p>
        </div>

        <div class="diag-box">
            <h3>Database Tables</h3>
            <ul>
                <?php
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($tables as $t) {
                    $count = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
                    echo "<li>$t: <strong>$count rows</strong></li>";
                }
                ?>
            </ul>
        </div>
    </div>
</body>
</html>
