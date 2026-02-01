<?php
// api/db_cms.php
require_once 'config.php';

echo "<h1>CMS Database Updater</h1>";
echo "<pre>";

try {
    $pdo = getDB();

    // SQLite Compatibility Helper
    $is_sqlite = (DB_CONNECTION === 'sqlite');
    $auto_inc = $is_sqlite ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT AUTO_INCREMENT PRIMARY KEY";
    $engine = $is_sqlite ? "" : "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $unique = $is_sqlite ? "UNIQUE" : "UNIQUE"; // Same

    // 1. Content Table (Movies & Series)
    echo "Checking 'local_content' table... ";
    $sql_content = "CREATE TABLE IF NOT EXISTS local_content (
        id $auto_inc,
        tmdb_id INT NOT NULL $unique,
        type VARCHAR(10) NOT NULL,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL $unique,
        overview TEXT,
        poster_path VARCHAR(255),
        backdrop_path VARCHAR(255),
        release_date DATE,
        vote_average DECIMAL(3, 1),
        
        seo_title VARCHAR(255),
        seo_description TEXT,
        is_featured TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $engine";
    $pdo->exec($sql_content);
    echo "OK\n";

    // 2. Comments Table
    echo "Checking 'comments' table... ";
    $sql_comments = "CREATE TABLE IF NOT EXISTS comments (
        id $auto_inc,
        user_id INT NOT NULL,
        tmdb_id INT NOT NULL,
        type VARCHAR(10) NOT NULL,
        comment TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $engine";
    $pdo->exec($sql_comments);
    echo "OK\n";

    // 3. Settings Table (For SMTP/Auth)
    echo "Checking 'settings' table... ";
    $sql_settings = "CREATE TABLE IF NOT EXISTS settings (
        id $auto_inc,
        setting_key VARCHAR(50) NOT NULL $unique,
        setting_value TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $engine";
    $pdo->exec($sql_settings);
    echo "OK\n";

    echo "CMS Schema Updated Successfully!\n";
    echo "<a href='../admin/index.php'>Go to Admin Panel</a>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
echo "</pre>";
?>
