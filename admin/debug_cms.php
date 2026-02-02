<?php
// admin/debug_cms.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>CMS Debugger</h1>";

echo "<h2>1. Path Check</h2>";
$configPath = '../api/config.php';
echo "Path to config: $configPath <br>";
if (file_exists($configPath)) {
    echo "Config found.<br>";
    require_once $configPath;
    echo "Config loaded.<br>";
} else {
    die("CRITICAL: Config NOT found.");
}

echo "<h2>2. Session Check</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "Session started.<br>";
} else {
    echo "Session already active (Role: " . ($_SESSION['role'] ?? 'None') . ")<br>";
}

echo "<h2>3. Database Check</h2>";
try {
    $pdo = getDB();
    echo "DB Connection OK.<br>";
    
    echo "<h2>4. Table Check: custom_pages</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'custom_pages'");
    if ($stmt->fetch()) {
        echo "Table 'custom_pages' EXISTS.<br>";
        
        $count = $pdo->query("SELECT COUNT(*) FROM custom_pages")->fetchColumn();
        echo "Row count: $count<br>";
    } else {
        echo "<span style='color:red'>Table 'custom_pages' DOES NOT EXIST.</span><br>";
        // Attempt fix
        echo "Attempting to create table...<br>";
        $pdo->exec("CREATE TABLE IF NOT EXISTS custom_pages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content LONGTEXT,
            seo_title VARCHAR(255),
            seo_description TEXT,
            is_published TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "Table created.<br>";
    }
    
} catch (Exception $e) {
    echo "<span style='color:red'>DB Error: " . $e->getMessage() . "</span>";
}
?>
