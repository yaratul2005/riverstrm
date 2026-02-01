<?php
// watch.php
require_once 'api/tmdb.php';

$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'movie';

if (!$id) {
    echo "<div class='section'><p>No content selected.</p></div>";
    exit;
}

$tmdb = new TMDB();
$details = $tmdb->getDetails($id, $type);

if (!$details) {
    echo "<div class='section'><p>Content not found.</p></div>";
    exit;
}

$title = $details['title'] ?? $details['name'];
$date = $details['release_date'] ?? $details['first_air_date'] ?? '';
$backdrop = $details['backdrop_path'];
?>

<div class="player-container">
    <iframe src="<?php echo RIVESTREAM_URL; ?>?type=<?php echo $type; ?>&id=<?php echo $id; ?>" allowfullscreen></iframe>
</div>

<section class="section">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <h1 style="font-size: 2.5rem; margin-bottom: 10px;"><?php echo htmlspecialchars($title); ?></h1>
        <div class="meta" style="color: #888; margin-bottom: 20px;">
            <span><?php echo substr($date, 0, 4); ?></span> • 
            <span><?php echo ucfirst($type); ?></span> • 
            <span>★ <?php echo $details['vote_average']; ?></span>
        </div>
        <p style="line-height: 1.6; max-width: 800px;"><?php echo htmlspecialchars($details['overview']); ?></p>
        
        <div style="margin-top: 30px;">
            <button class="btn">Add to Favorites</button> <!-- Needs backend logic later -->
        </div>

        <?php if (!empty($details['similar']['results'])): ?>
            <div class="section-title" style="margin-top: 50px;">You May Also Like</div>
            <div class="media-grid">
                <?php foreach (array_slice($details['similar']['results'], 0, 6) as $item): 
                     $sTitle = $item['title'] ?? $item['name'];
                     $sDate = $item['release_date'] ?? $item['first_air_date'] ?? '';
                ?>
                    <a href="index.php?page=watch&type=<?php echo $type; ?>&id=<?php echo $item['id']; ?>" class="media-card">
                        <img src="https://image.tmdb.org/t/p/w500<?php echo $item['poster_path']; ?>" alt="<?php echo htmlspecialchars($sTitle); ?>" loading="lazy">
                        <div class="info">
                            <h3><?php echo htmlspecialchars($sTitle); ?></h3>
                            <span><?php echo substr($sDate, 0, 4); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
