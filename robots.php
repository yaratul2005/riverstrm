<?php
// robots.php
header("Content-Type: text/plain");
require_once 'api/config.php';
$baseUrl = SITE_URL; // e.g. https://great10.xyz

echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /api/\n";
echo "Disallow: /includes/\n";
echo "Disallow: /install/\n";
echo "\n";
echo "Sitemap: {$baseUrl}/sitemap.php";
?>
