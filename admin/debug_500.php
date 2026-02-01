<?php
// admin/debug_500.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "1. Starting Debug...\n";

$configFile = __DIR__ . '/../api/config.php';
if (!file_exists($configFile)) {
    die("CRITICAL: Config file not found at $configFile\n");
}
echo "2. Config file found.\n";

require_once $configFile;
echo "3. Config loaded.\n";

try {
    echo "4. Attempting getDB()...\n";
    $pdo = getDB();
    echo "5. DB Connected.\n";
} catch (Throwable $e) {
    die("DB Connection Error: " . $e->getMessage() . "\n");
}

echo "6. Testing Queries...\n";

$queries = [
    'users_count' => "SELECT COUNT(*) FROM users",
    'movies_count' => "SELECT COUNT(*) FROM local_content WHERE type='movie'",
    'series_count' => "SELECT COUNT(*) FROM local_content WHERE type='tv'",
    'comments_count' => "SELECT COUNT(*) FROM comments",
    'recent_users' => "SELECT * FROM users ORDER BY created_at DESC LIMIT 5",
    'recent_content' => "SELECT * FROM local_content ORDER BY created_at DESC LIMIT 5"
];

foreach ($queries as $name => $sql) {
    echo "   Testing $name... ";
    try {
        $pdo->query($sql);
        echo "OK\n";
    } catch (Throwable $e) {
        echo "FAIL: " . $e->getMessage() . "\n";
    }
}

echo "7. Debug Complete.\n";
?>
