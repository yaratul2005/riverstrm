<?php
// watch.php
// Assumes $pdo is available

if (!isset($_GET['id'])) {
    echo "Content ID missing.";
    exit;
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM content WHERE id = ?");
$stmt->execute([$id]);
$content = $stmt->fetch();

if (!$content) {
    echo "Content not found.";
    exit;
}

$tmdbId = $content['tmdb_id'];
$type = $content['type']; // 'movie' or 'series'

?>
<div class="player-container">
    <iframe src="<?php echo RIVESTREAM_URL; ?>?type=<?php echo $type; ?>&id=<?php echo $tmdbId; ?>" allowfullscreen></iframe>
</div>

<section class="section">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 2.5rem; margin-bottom: 10px;"><?php echo htmlspecialchars($content['title']); ?></h1>
        <div class="meta" style="color: #888; margin-bottom: 20px;">
            <span><?php echo substr($content['release_date'], 0, 4); ?></span> • 
            <span><?php echo ucfirst($type); ?></span> • 
            <span>★ <?php echo $content['rating']; ?></span>
        </div>
        <p style="line-height: 1.6; max-width: 800px;"><?php echo htmlspecialchars($content['overview']); ?></p>
        
        <div style="margin-top: 30px;">
            <button class="btn">Add to Favorites</button>
            <button class="btn">Trailer</button>
        </div>
    </div>
</section>

<!-- Related implementation could go here (fetching by genre from TMDB or similar in DB) -->
