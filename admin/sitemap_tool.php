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
    
    $offset = ($batch - 1) * $batchSize;
    $filename = "../sitemap{$batch}.xml";
    $baseUrl = SITE_URL;

    // Fetch Content
    // We only fetch 'local_content' (Movies/Series) for now as requested
    $stmt = $pdo->prepare("SELECT * FROM local_content WHERE is_active = 1 ORDER BY id ASC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $batchSize, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll();

    if (count($items) > 0) {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($items as $c) {
            $url = "{$baseUrl}/index.php?page=watch&type={$c['type']}&id={$c['tmdb_id']}";
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
            $msg = "✅ Generated <strong>sitemap{$batch}.xml</strong> with " . count($items) . " links.";
        } else {
            $msg = "❌ Error writing file. Check permissions.";
        }
    } else {
        $msg = "⚠️ No content found for Batch {$batch} (Offset {$offset}).";
    }
}
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

        <form method="POST">
            <label style="display: block; color: #ccc; margin-bottom: 5px;">Batch Number</label>
            <input type="number" name="batch" value="<?php echo isset($_POST['batch']) ? $_POST['batch'] + 1 : 1; ?>" min="1" required>
            <div style="font-size: 0.8rem; color: #666;">(e.g., 1 = Links 1-50, 2 = Links 51-100)</div>
            
            <button type="submit" class="btn-gen">Generate Static Sitemap</button>
        </form>
        
        <div style="margin-top: 20px; border-top: 1px solid #333; padding-top: 15px;">
            <a href="index.php" style="color: #888; text-decoration: none;">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
