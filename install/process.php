<?php
// install/process.php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$db_host = trim($_POST['db_host']);
$db_name = trim($_POST['db_name']);
$db_user = trim($_POST['db_user']);
$db_pass = trim($_POST['db_pass']);

$admin_email = trim($_POST['admin_email']);
$admin_pass = trim($_POST['admin_pass']);
$site_url = rtrim(trim($_POST['site_url']), '/');

// 1. Validate Database Connection
try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error = "Database Connection Failed: " . $e->getMessage();
    header('Location: index.php?error=' . urlencode($error));
    exit;
}

// 2. Create Schema
try {
    // Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Content Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tmdb_id INT UNIQUE NOT NULL,
        title VARCHAR(255) NOT NULL,
        overview TEXT,
        poster_path VARCHAR(255),
        backdrop_path VARCHAR(255),
        type VARCHAR(20) NOT NULL,
        release_date DATE,
        rating DECIMAL(3,1),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

} catch (PDOException $e) {
    $error = "Table Creation Failed: " . $e->getMessage();
    header('Location: index.php?error=' . urlencode($error));
    exit;
}

// 3. Create Admin User
try {
    $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
    // Check if admin exists first to avoid duplicate errors on re-install
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $check->execute([$admin_email]);
    if ($check->fetchColumn() == 0) {
        $stmt->execute(['Admin', $admin_email, $hash]);
    }
} catch (PDOException $e) {
    // Ignore if admin already exists or handle specifically
}

// 4. Write Config File
$config_content = "<?php
// api/config.php

// Error Reporting (Turn off for production)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('SITE_NAME', 'Great10 Streaming');
define('SITE_URL', '$site_url');
define('TMDB_API_KEY', '667911222fb9399f2d4bec7e5f4d548b');
define('RIVESTREAM_URL', 'https://rivestream.pages.dev/embed');

// Database Configuration
define('DB_CONNECTION', 'mysql');
define('DB_HOST', '$db_host');
define('DB_NAME', '$db_name');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass');

function getDB() {
    try {
        \$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\";
        \$pdo = new PDO(\$dsn, DB_USER, DB_PASS);
        \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return \$pdo;
    } catch (PDOException \$e) {
        die(\"Connection failed: \" . \$e->getMessage());
    }
}
?>";

if (file_put_contents('../api/config.php', $config_content) === false) {
    $error = "Failed to write config file. Please check permissions of api/ directory.";
    header('Location: index.php?error=' . urlencode($error));
    exit;
}

// 5. Success
echo "<div style='text-align: center; margin-top: 50px; font-family: sans-serif;'>
        <h1 style='color: green;'>Installation Successful!</h1>
        <p>Your website is now ready.</p>
        <p><strong>Admin Email:</strong> $admin_email</p>
        <p><a href='$site_url' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Website</a></p>
        <p style='color: red; margin-top: 20px;'>Please delete the 'install' folder for security.</p>
      </div>";
?>
