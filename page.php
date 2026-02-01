<?php
// page.php
require_once 'api/config.php';
$pdo = getDB();

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM custom_pages WHERE slug = ? AND is_published = 1");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    header("HTTP/1.0 404 Not Found");
    include '404.php'; // Optional: fallback
    die("<h1>Page not found</h1>");
}
?>
<!-- SEO Injection via JS shim or direct output -->
<script>
    document.title = "<?php echo htmlspecialchars($page['seo_title'] ?: $page['title']); ?> - Great10";
    let desc = document.querySelector('meta[name="description"]');
    if (!desc) { desc = document.createElement('meta'); desc.name="description"; document.head.appendChild(desc); }
    desc.content = "<?php echo htmlspecialchars($page['seo_description']); ?>";
</script>

<div class="page-container" style="max-width: 900px; margin: 50px auto; padding: 0 5%;">
    <h1 style="font-size: 2.5rem; margin-bottom: 20px; text-align: center;"><?php echo htmlspecialchars($page['title']); ?></h1>
    <div class="page-content" style="background: rgba(20,20,20,0.6); padding: 40px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); line-height: 1.8; font-size: 1.1rem; color: #ddd;">
        <?php echo $page['content']; // Rich Text (Trust Admin Input) ?>
    </div>
</div>
