<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'api/config.php';
echo "Config loaded.<br>";
require_once 'api/tmdb.php';
echo "TMDB loaded.<br>";

$tmdb = new TMDB();
echo "TMDB initialized.<br>";

$data = $tmdb->get('discover/movie', ['page'=>1]);
echo "API Test: " . (isset($data['results']) ? 'Success' : 'Fail');
?>
