<?php
// api/config.php

// Error Reporting (Turn off for production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start Session
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Configuration
define('SITE_NAME', 'Great10 Streaming');
define('SITE_URL', 'http://localhost:8000'); // Update this based on environment
define('TMDB_API_KEY', '667911222fb9399f2d4bec7e5f4d548b');
define('RIVESTREAM_URL', 'https://rivestream.pages.dev/embed'); // Placeholder base

// Database Configuration
define('DB_CONNECTION', 'mysql'); // Options: 'sqlite', 'mysql'

// MySQL Settings (only used if DB_CONNECTION is mysql)
define('DB_HOST', 'localhost');
define('DB_NAME', 'great10_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// SQLite Settings
define('DB_FILE', __DIR__ . '/../db/database.sqlite');

function getDB() {
    try {
        if (DB_CONNECTION === 'sqlite') {
            // Ensure db directory exists
            $dbDir = dirname(DB_FILE);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            
            $pdo = new PDO('sqlite:' . DB_FILE);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Create tables if they don't exist (Migration for SQLite)
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                role TEXT DEFAULT 'user', -- user, admin
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $pdo->exec("CREATE TABLE IF NOT EXISTS content (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                tmdb_id INTEGER UNIQUE NOT NULL,
                title TEXT NOT NULL,
                overview TEXT,
                poster_path TEXT,
                backdrop_path TEXT,
                type TEXT NOT NULL, -- movie, series
                release_date TEXT,
                rating REAL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Default Admin (password: admin123)
            // You should change this immediately
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            $stmt->execute();
            if ($stmt->fetchColumn() == 0) {
                 $pass = password_hash('admin123', PASSWORD_DEFAULT);
                 $pdo->exec("INSERT INTO users (username, email, password, role) VALUES ('Admin', 'admin@great10.xyz', '$pass', 'admin')");
            }

            return $pdo;
        } elseif (DB_CONNECTION === 'mysql') {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>
