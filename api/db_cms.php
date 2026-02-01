<?php
// api/db_cms.php
require_once 'config.php';

echo "<h1>CMS Database Updater</h1>";
echo "<pre>";

try {
    $pdo = getDB();

    // 1. Content Table (Movies & Series)
    // We use a single table 'content' for local curation
    // This allows us to "Import" items from TMDB and override their details
    echo "Checking 'content' table... ";
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

    echo "CMS Schema Updated Successfully!\n";
    echo "<a href='../admin/index.php'>Go to Admin Panel</a>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
echo "</pre>";
?>
