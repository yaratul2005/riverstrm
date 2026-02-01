<?php
// watch.php
require_once 'api/tmdb.php';

$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'movie';
$season = $_GET['season'] ?? 1;
$episode = $_GET['episode'] ?? 1;

if (!$id) {
    echo "<div style='color:white; padding: 100px; text-align:center;'>No content selected.</div>";
    exit;
}

$tmdb = new TMDB();
$details = $tmdb->getDetails($id, $type);

if (!$details) {
    echo "<div style='color:white; padding: 100px; text-align:center;'>Content not found.</div>";
    exit;
}

$title = $details['title'] ?? $details['name'];
$date = $details['release_date'] ?? $details['first_air_date'] ?? '';
$backdrop = $details['backdrop_path'];
$rating = $details['vote_average'];

// Handle Series Logic
$episodes = [];
$total_seasons = 0;
if ($type === 'tv' || $type === 'series') {
    $type = 'tv'; // TMDB uses 'tv'
    $total_seasons = $details['number_of_seasons'] ?? 1;
    
    // Fetch Season Details
    $seasonData = $tmdb->getSeasonDetails($id, $season);
    $episodes = $seasonData['episodes'] ?? [];
    
    // Rivestream uses 'series' for type
    $playerType = 'series';
} else {
    $playerType = 'movie';
}

// Construct Player URL
$playerUrl = RIVESTREAM_URL . "?type=$playerType&id=$id";
if ($playerType === 'series') {
    $playerUrl .= "&season=$season&episode=$episode";
}
?>

<!-- Player Wrapper -->
<div class="player-container">
    <iframe src="<?php echo $playerUrl; ?>" allowfullscreen scrolling="no" frameborder="0"></iframe>
</div>

<!-- Content Info -->
<div class="watch-meta">
    <h1 style="font-size: clamp(1.5rem, 4vw, 2.5rem); margin-bottom: 10px;"><?php echo htmlspecialchars($title); ?></h1>
    
    <div style="color: #a3a3a3; font-size: 0.9rem; margin-bottom: 20px; display: flex; gap: 15px; align-items: center;">
        <span><?php echo substr($date, 0, 4); ?></span>
        <span style="border: 1px solid #444; padding: 2px 6px; border-radius: 3px; font-size: 0.8rem;"><?php echo strtoupper($type); ?></span>
        <span style="color: #e50914;">★ <?php echo number_format($rating, 1); ?></span>
    </div>

    <p style="color: #ccc; max-width: 800px; margin-bottom: 30px; line-height: 1.6;">
        <?php echo htmlspecialchars($details['overview']); ?>
    </p>

    <!-- Series: Season & Episode Selector -->
    <?php if ($type === 'tv'): ?>
        <hr style="border-color: #333; margin-bottom: 30px;">
        
        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
            <h3 style="margin: 0;">Episodes</h3>
            <select class="season-selector" onchange="location = this.value;">
                <?php for($i = 1; $i <= $total_seasons; $i++): ?>
                    <option value="index.php?page=watch&type=tv&id=<?php echo $id; ?>&season=<?php echo $i; ?>" <?php echo $i == $season ? 'selected' : ''; ?>>
                        Season <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="episode-list">
            <?php foreach ($episodes as $ep): 
                $epImg = $ep['still_path'] ? "https://image.tmdb.org/t/p/w300" . $ep['still_path'] : "https://via.placeholder.com/300x169/111/fff?text=No+Image";
                $isCurrent = ($ep['episode_number'] == $episode);
            ?>
                <a href="index.php?page=watch&type=tv&id=<?php echo $id; ?>&season=<?php echo $season; ?>&episode=<?php echo $ep['episode_number']; ?>" 
                   class="episode-card <?php echo $isCurrent ? 'active' : ''; ?>"
                   style="<?php echo $isCurrent ? 'border-color: var(--accent-color);' : ''; ?>">
                    <img src="<?php echo $epImg; ?>" alt="Ep <?php echo $ep['episode_number']; ?>" loading="lazy">
                    <div class="episode-info">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span class="episode-number"><?php echo $ep['episode_number']; ?>. <?php echo htmlspecialchars($ep['name']); ?></span>
                        </div>
                        <p style="font-size: 0.8rem; color: #888; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            <?php echo htmlspecialchars($ep['overview']); ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Similar Content -->
    <?php if (!empty($details['similar']['results'])): ?>
        <hr style="border-color: #333; margin: 50px 0 30px;">
        <h3 class="section-title">You May Also Like</h3>
        <div class="media-grid">
            <?php foreach (array_slice($details['similar']['results'], 0, 6) as $item): 
                    $sTitle = $item['title'] ?? $item['name'];
                    $sImg = $item['poster_path'] ? "https://image.tmdb.org/t/p/w300" . $item['poster_path'] : "";
                    if (!$sImg) continue;
            ?>
                <a href="index.php?page=watch&type=<?php echo $type; ?>&id=<?php echo $item['id']; ?>" class="media-card">
                    <img src="<?php echo $sImg; ?>" alt="<?php echo htmlspecialchars($sTitle); ?>" loading="lazy">
                    <div class="info">
                        <h3><?php echo htmlspecialchars($sTitle); ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
