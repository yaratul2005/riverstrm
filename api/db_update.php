<?php
// api/db_update.php
require_once 'config.php';

echo "<h1>Database Updater</h1>";
echo "<pre>";

try {
    $pdo = getDB();
    
    // 1. Settings Table
    echo "Checking 'settings' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "OK\n";

    // 2. User Verifications Table
    echo "Checking 'user_verifications' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_verifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(64) NOT NULL,
        type VARCHAR(20) DEFAULT 'activation', -- activation, reset
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "OK\n";

    // 3. Update Users Table (Add columns if not exist)
    echo "Checking 'users' columns... \n";
    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('google_id', $columns)) {
        echo " - Adding 'google_id'... ";
        $pdo->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(100) UNIQUE NULL AFTER password");
        echo "OK\n";
    }

    if (!in_array('is_verified', $columns)) {
        echo " - Adding 'is_verified'... ";
        $pdo->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER role");
        echo "OK\n";
    }

    if (!in_array('avatar', $columns)) {
        echo " - Adding 'avatar'... ";
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL AFTER email");
        echo "OK\n";
    }
    
    echo "Database Up To Date!\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

echo "</pre>";
echo "<a href='../admin/index.php'>Go to Admin Panel</a>";
?>
