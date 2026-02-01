<?php
// api/db_cms.php
require_once 'config.php';

echo "<h1>CMS Database Updater (MySQL)</h1>";
echo "<pre>";

try {
    // 0. Connect to MySQL Server (No DB selected) to create DB
    $dsn_no_db = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo_root = new PDO($dsn_no_db, DB_USER, DB_PASS);
    $pdo_root->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_root->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "Database '" . DB_NAME . "' checked/created.<br>";

    // Now connect with DB selected
    $pdo = getDB();

    // 1. Content Table (Movies & Series)
    echo "Checking 'local_content' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS local_content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tmdb_id INT NOT NULL UNIQUE,
        type VARCHAR(10) NOT NULL, -- 'movie' or 'tv'
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        overview TEXT,
        poster_path VARCHAR(255),
        backdrop_path VARCHAR(255),
        release_date DATE,
        vote_average DECIMAL(3, 1),
        
        -- SEO & CMS Fields
        seo_title VARCHAR(255),
        seo_description TEXT,
        is_featured TINYINT(1) DEFAULT 0, -- For Home Slider
        is_active TINYINT(1) DEFAULT 1,
        
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (type),
        INDEX (is_featured)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "OK\n";

    // 2. Comments Table
    echo "Checking 'comments' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        tmdb_id INT NOT NULL,
        type VARCHAR(10) NOT NULL,
        comment TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (tmdb_id),
        INDEX (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "OK\n";

    // 3. Settings Table
    echo "Checking 'settings' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "OK\n";
    
    // 4. Custom Pages Table (For CMS)
    echo "Checking 'custom_pages' table... ";
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
    echo "OK\n";

    // 5. Users Table (Ensure it exists for Admin)
    echo "Checking 'users' table... ";
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "OK\n";

    // Create Default Admin if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pass = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (username, email, password, role) VALUES ('Admin', 'admin@great10.xyz', '$pass', 'admin')");
        echo "Default Admin Account Created (Email: admin@great10.xyz, Pass: admin123)\n";
    }

    echo "CMS Schema Updated Successfully!\n";
    echo "<a href='../admin/index.php'>Go to Admin Panel</a>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    echo "\n\nMake sure you have created the database 'great10_db' in your MySQL server!";
}
echo "</pre>";
?>
