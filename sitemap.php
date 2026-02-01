<?php
// sitemap.php
require_once 'api/config.php';
header("Content-Type: application/xml; charset=utf-8");

$pdo = getDB();
$baseUrl = SITE_URL; // e.g. https://great10.xyz

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- Static Home -->
    <url>
        <loc><?php echo $baseUrl; ?>/</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <?php
    // 1. Curated Content (Movies/Series)
    $content = $pdo->query("SELECT * FROM local_content WHERE is_active = 1")->fetchAll();
    foreach ($content as $c) {
        $id = $c['tmdb_id'];
        $type = $c['type'];
        // URL Structure: index.php?page=watch&type=movie&id=123
        $url = "{$baseUrl}/index.php?page=watch&type={$type}&id={$id}";
        $date = date('Y-m-d', strtotime($c['updated_at']));
        echo "
        <url>
            <loc>{$url}</loc>
            <lastmod>{$date}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>";
    }

    // 2. Custom Pages
    $pages = $pdo->query("SELECT * FROM custom_pages WHERE is_published = 1")->fetchAll();
    foreach ($pages as $p) {
        $slug = $p['slug'];
        $url = "{$baseUrl}/index.php?page=page&slug={$slug}"; // Assuming index routing handles this or user rewrites
        // Actual link logic used in site is separate, let's assume index.php?page=... handler
        
        // Fix: If routing is page.php directly? 
        // Note: The site structure uses index.php?page=... so we need to ensure router handles 'page'
        // But for now let's map to where we put page.php logic.
        // Actually I put page.php as standalone. 
        // Let's use direct link for now.
        $url = "{$baseUrl}/page.php?slug={$slug}";
        
        $date = date('Y-m-d', strtotime($p['updated_at']));
         echo "
        <url>
            <loc>{$url}</loc>
            <lastmod>{$date}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.5</priority>
        </url>";
    }
    ?>
</urlset>
