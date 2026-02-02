<?php
// admin/sitemap_tool.php
require_once '../api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$pdo = getDB();
$msg = '';
$batchSize = 50;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $batch = (int)$_POST['batch'];
    if ($batch < 1) $batch = 1;
    $source = $_POST['source'] ?? 'db';
    
    $filename = "../sitemap{$batch}.xml";
    $baseUrl = SITE_URL;
    if ($source === 'db') {
        // Fetch Content from Local DB (Limit/Offset)
        $offset = ($batch - 1) * $batchSize;
        $stmt = $pdo->prepare("SELECT * FROM local_content WHERE is_active = 1 ORDER BY id ASC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $batchSize, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Fetch Content from TMDB API (Page = Batch)
        require_once '../api/tmdb.php';
        $tmdb = new TMDB();
        
        // TMDB pages are 1-indexed, so Batch 1 = Page 1
        $data = $tmdb->getPopular('movie', $batch); // Fetch Movies
        $items = $data['results'] ?? [];
        
        // Standardize structure
        $tempItems = [];
        foreach ($items as $m) {
            $tempItems[] = [
                'tmdb_id' => $m['id'],
                'type'    => 'movie',
                'updated_at' => $m['release_date'] ?? date('Y-m-d')
            ];
        }
        $items = $tempItems;
        
        // Also fetch Series? Maybe later. Just Movies for now as requested "draw movies".
    }

    if (count($items) > 0) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($items as $c) {
            $rawUrl = "{$baseUrl}/index.php?page=watch&type={$c['type']}&id={$c['tmdb_id']}";
            $url = htmlspecialchars($rawUrl, ENT_XML1, 'UTF-8');
            $date = date('Y-m-d', strtotime($c['updated_at']));
            $xml .= "    <url>\n";
            $xml .= "        <loc>{$url}</loc>\n";
            $xml .= "        <lastmod>{$date}</lastmod>\n";
            $xml .= "        <changefreq>weekly</changefreq>\n";
            $xml .= "        <priority>0.8</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';

        if (file_put_contents($filename, $xml)) {
            $sourceStr = ($source === 'tmdb') ? 'TMDB API (Popular)' : 'Local Database';
            $msg = "✅ Generated <strong>sitemap{$batch}.xml</strong> from {$sourceStr} with " . count($items) . " links.";
        } else {
            $msg = "❌ Error writing file. Check permissions.";
        }
    } else {
        $msg = "⚠️ No content found for Batch {$batch}.";
    }
}
    
    // Count total items
    $total = $pdo->query("SELECT COUNT(*) FROM local_content WHERE is_active = 1")->fetchColumn();
    $maxBatches = ceil($total / $batchSize);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sitemap Generator</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #050505; color: white; display: flex; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif; }
        .box { background: #161616; padding: 40px; border-radius: 12px; border: 1px solid #333; width: 400px; text-align: center; }
        input[type="number"] { padding: 10px; background: #000; border: 1px solid #444; color: white; width: 80px; text-align: center; font-size: 1.2rem; border-radius: 4px; margin: 10px 0; }
        .btn-gen { background: var(--accent-color); color: white; padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; width: 100%; margin-top: 15px; }
        .btn-gen:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="box">
        <h1 style="margin-bottom: 5px;">Sitemap Generator</h1>
        <p style="color: #888; margin-bottom: 25px;">Generate static XML files in batches of 50.</p>
        
        <?php if ($msg): ?>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem;">
                <?php echo $msg; ?>
            </div>
            <?php if (strpos($msg, 'Generated') !== false): ?>
                <p style="margin-bottom: 20px;"><a href="../sitemap<?php echo $batch; ?>.xml" target="_blank" style="color: #4caf50;">View sitemap<?php echo $batch; ?>.xml</a></p>
            <?php endif; ?>
        <?php endif; ?>

        <div style="margin-bottom: 20px; font-size: 0.9rem; color: #aaa; background: #222; padding: 10px; border-radius: 6px;">
            Total Curated Content: <strong><?php echo $total; ?></strong><br>
            Max Batch Number: <strong><?php echo $maxBatches; ?></strong>
        </div>

        <form method="POST">
            <div style="margin-bottom: 20px; text-align: left;">
                <label style="color: #ccc; display: block; margin-bottom: 10px;">Source:</label>
                <label style="display: block; margin-bottom: 5px; color: #888; cursor: pointer;">
                    <input type="radio" name="source" value="db" checked> Local Database (Curated)
                </label>
                <label style="display: block; margin-bottom: 5px; color: #888; cursor: pointer;">
                    <input type="radio" name="source" value="tmdb"> TMDB API (Popular Movies)
                </label>
            </div>

            <label style="display: block; color: #ccc; margin-bottom: 5px;">Batch Number / Page Number</label>
            <input type="number" name="batch" value="<?php echo isset($_POST['batch']) ? $_POST['batch'] + 1 : 1; ?>" min="1" max="500" required>
            <div style="font-size: 0.8rem; color: #666;">(For TMDB: Batch 1 = Popular Page 1)</div>
            
            <button type="submit" class="btn-gen">Generate Static Sitemap</button>
        </form>
        
        <div style="margin-top: 20px; border-top: 1px solid #333; padding-top: 15px;">
            <a href="index.php" style="color: #888; text-decoration: none;">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
