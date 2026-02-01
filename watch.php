<?php
// watch.php
require_once 'api/tmdb.php';

$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'movie';
$season = $_GET['season'] ?? 1;
$episode = $_GET['episode'] ?? 1;

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
$rating = $details['vote_average'];

// Handle Series Logic
$episodes = [];
$total_seasons = 0;
if ($type === 'tv' || $type === 'series') {
    $type = 'tv';
    $total_seasons = $details['number_of_seasons'] ?? 1;
    $seasonData = $tmdb->getSeasonDetails($id, $season);
    $episodes = $seasonData['episodes'] ?? [];
    $playerType = 'series';
} else {
    $playerType = 'movie';
}

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
    <h1 style="font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 800; margin-bottom: 10px; text-shadow: 0 0 20px rgba(255,255,255,0.1);"><?php echo htmlspecialchars($title); ?></h1>
    
    <div style="color: #a3a3a3; font-size: 0.95rem; margin-bottom: 25px; display: flex; gap: 15px; align-items: center;">
        <span><?php echo substr($date, 0, 4); ?></span>
        <span style="border: 1px solid rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; background: rgba(255,255,255,0.05);"><?php echo strtoupper($type); ?></span>
        <span style="color: var(--accent-color); font-weight: bold;">★ <?php echo number_format($rating, 1); ?></span>
    </div>

    <p style="color: #ccc; max-width: 800px; margin-bottom: 40px; line-height: 1.7; font-size: 1.05rem;">
        <?php echo htmlspecialchars($details['overview']); ?>
    </p>

    <!-- Series Logic -->
    <?php if ($type === 'tv'): ?>
        <div style="background: var(--bg-secondary); padding: 25px; border-radius: 12px; border: var(--glass-border);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 1.2rem;">Episodes</h3>
                <select class="season-selector" onchange="location = this.value;" style="background: #000; color: white; border: 1px solid #333; padding: 8px 15px; border-radius: 6px;">
                    <?php for($i = 1; $i <= $total_seasons; $i++): ?>
                        <option value="index.php?page=watch&type=tv&id=<?php echo $id; ?>&season=<?php echo $i; ?>" <?php echo $i == $season ? 'selected' : ''; ?>>
                            Season <?php echo $i; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="episode-list">
                <?php foreach ($episodes as $ep): 
                    $epImg = $ep['still_path'] ? "https://image.tmdb.org/t/p/w300" . $ep['still_path'] : "https://via.placeholder.com/300x169/111/fff?text=No+Preview";
                    $isCurrent = ($ep['episode_number'] == $episode);
                ?>
                    <a href="index.php?page=watch&type=tv&id=<?php echo $id; ?>&season=<?php echo $season; ?>&episode=<?php echo $ep['episode_number']; ?>" 
                       class="episode-card <?php echo $isCurrent ? 'active' : ''; ?>"
                       style="<?php echo $isCurrent ? 'border-color: var(--accent-color); box-shadow: 0 0 15px rgba(229,9,20,0.3);' : ''; ?>">
                        <img src="<?php echo $epImg; ?>" alt="Ep <?php echo $ep['episode_number']; ?>" loading="lazy">
                        <div class="episode-info">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span class="episode-number" style="color: <?php echo $isCurrent ? 'var(--accent-color)' : '#fff'; ?>"><?php echo $ep['episode_number']; ?>. <?php echo htmlspecialchars($ep['name']); ?></span>
                            </div>
                            <p style="font-size: 0.8rem; color: #888; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?php echo htmlspecialchars($ep['overview']); ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Comments Section -->
    <div class="comments-container">
        <h3 class="section-title" style="margin-bottom: 20px;">Discussion</h3>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <form id="commentForm" class="comment-form">
                <input type="hidden" name="tmdb_id" value="<?php echo $id; ?>">
                <input type="hidden" name="type" value="<?php echo $type; ?>">
                <textarea name="comment" rows="3" placeholder="Join the discussion..." required></textarea>
                <button type="submit" class="btn btn-primary">Post Comment</button>
            </form>
        <?php else: ?>
            <p style="color: #888; margin-bottom: 20px;"><a href="index.php?page=login" style="color: white; text-decoration: underline;">Login</a> to post a comment.</p>
        <?php endif; ?>

        <div id="commentList" class="comment-list" data-tmdb-id="<?php echo $id; ?>">
            <!-- Comments loaded via JS -->
            <div style="text-align: center; color: #666; padding: 20px;">Loading comments...</div>
        </div>
    </div>

    <!-- Similar Content -->
    <?php if (!empty($details['similar']['results'])): ?>
        <div style="margin-top: 60px;">
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
        </div>
    <?php endif; ?>
</div>
