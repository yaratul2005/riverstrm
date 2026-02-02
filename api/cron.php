<?php
// api/cron.php
// Usage: curl https://your-site.com/api/cron.php?secret=YOUR_SECRET>&job=all
require_once 'config.php';

header('Content-Type: application/json');

// 1. Security Check
$secret = $_GET['secret'] ?? '';
if ($secret !== CRON_SECRET) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized Access']);
    exit;
}

$job = $_GET['job'] ?? 'all';
$pdo = getDB();
$results = [];

// JOB: Sitemap Generator (Auto-generates sitemap1.xml for first 200 items)
if ($job === 'all' || $job === 'sitemap') {
    try {
        $baseUrl = SITE_URL;
        $stmt = $pdo->prepare("SELECT * FROM local_content WHERE is_active = 1 ORDER BY id DESC LIMIT 200");
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($items) > 0) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
            foreach ($items as $c) {
                // Determine ID key (TMDB vs Local)
                $cid = $c['tmdb_id'] ?? $c['id']; 
                $rawUrl = "{$baseUrl}/index.php?page=watch&type={$c['type']}&id={$cid}";
                $url = htmlspecialchars($rawUrl, ENT_XML1, 'UTF-8');
                $date = date('Y-m-d');
                $xml .= "    <url>\n";
                $xml .= "        <loc>{$url}</loc>\n";
                $xml .= "        <lastmod>{$date}</lastmod>\n";
                $xml .= "        <changefreq>daily</changefreq>\n";
                $xml .= "        <priority>0.9</priority>\n";
                $xml .= "    </url>\n";
            }
            $xml .= '</urlset>';
            
            // Save to root
            file_put_contents('../sitemap1.xml', $xml);
            $results['sitemap'] = 'Generated sitemap1.xml with ' . count($items) . ' items.';
        } else {
            $results['sitemap'] = 'No content to generate.';
        }
    } catch (Exception $e) {
        $results['sitemap_error'] = $e->getMessage();
    }
}

// JOB: Optimized Content (e.g. clean up broken images, old tokens)
if ($job === 'all' || $job === 'cleanup') {
    // Example: Delete old verification tokens
    // $pdo->exec("DELETE FROM user_tokens WHERE created_at < NOW() - INTERVAL 1 DAY");
    $results['cleanup'] = 'Cleaned up system logs (Placeholder).';
}

echo json_encode(['success' => true, 'jobs' => $results, 'timestamp' => date('c')]);
?>
