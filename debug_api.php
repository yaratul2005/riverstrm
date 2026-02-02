<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'api/config.php';
require_once 'api/tmdb.php';

$tmdb = new TMDB();

echo "<h1>TMDB API Debug</h1>";
echo "API Key: " . (defined('TMDB_API_KEY') ? 'Set' : 'Missing') . "<br>";

// Test Request
$endpoint = '/discover/movie';
$params = ['with_genres' => 16, 'page' => 1]; // Animation
echo "Requesting: $endpoint with genre 16...<br>";

try {
    $data = $tmdb->request($endpoint, $params);
    
    if ($data === null) {
        echo "<h3 style='color:red'>Response is NULL. cURL might have failed.</h3>";
    } elseif (isset($data['status_code'])) {
         echo "<h3 style='color:red'>API Error: " . htmlspecialchars($data['status_message']) . "</h3>";
    } elseif (!empty($data['results'])) {
        echo "<h3 style='color:green'>Success! Found " . count($data['results']) . " results.</h3>";
        echo "<pre>" . print_r($data['results'][0], true) . "</pre>";
    } else {
        echo "<h3 style='color:orange'>No results found (Results array empty).</h3>";
        echo "Raw Data: <pre>" . print_r($data, true) . "</pre>";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
?>
