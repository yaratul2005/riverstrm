<?php
// search.php
require_once 'api/tmdb.php';

$query = $_GET['q'] ?? '';
$results = [];

if ($query) {
    $tmdb = new TMDB();
    $data = $tmdb->search($query, 'multi'); // Multi search for movies & tv
    $results = $data['results'] ?? [];
}
?>

<div class="section" style="padding-top: 100px;">
    <div class="container" style="max-width: 800px; margin: 0 auto; text-align: center;">
        <h1 style="margin-bottom: 30px;">Find Movies & TV Shows</h1>
        <form method="GET" action="index.php">
            <input type="hidden" name="page" value="search">
            <input type="text" name="q" placeholder="Type to search..." value="<?php echo htmlspecialchars($query); ?>" 
                   style="width: 100%; padding: 20px; font-size: 1.2rem; background: #111; color: white; border: 1px solid #333; border-radius: 5px;">
        </form>
    </div>
</div>

<?php if ($query): ?>
    <section class="section">
        <div class="section-title">Results for "<?php echo htmlspecialchars($query); ?>"</div>
        <?php if (empty($results)): ?>
            <p style="text-align: center; color: #888;">No results found.</p>
        <?php else: ?>
            <div class="media-grid">
                <?php foreach ($results as $item): 
                    $type = $item['media_type'] ?? 'movie';
                    if ($type === 'person') continue; // Skip people

                    $title = $item['title'] ?? $item['name'];
                    $date = $item['release_date'] ?? $item['first_air_date'] ?? '';
                    $poster = $item['poster_path'] ?? '';
                    
                    if (!$poster) continue; // Skip items without images
                ?>
                    <a href="index.php?page=watch&type=<?php echo $type; ?>&id=<?php echo $item['id']; ?>" class="media-card">
                        <img src="https://image.tmdb.org/t/p/w500<?php echo $poster; ?>" alt="<?php echo htmlspecialchars($title); ?>" loading="lazy">
                        <div class="info">
                            <h3><?php echo htmlspecialchars($title); ?></h3>
                            <span><?php echo substr($date, 0, 4); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>
