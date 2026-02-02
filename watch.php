<?php
// watch.php
require_once 'api/tmdb.php';

$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'movie';
$season = $_GET['season'] ?? 1;
$episode = $_GET['episode'] ?? 1;

if (!$id) { exit("<p>No content selected.</p>"); }

$tmdb = new TMDB();
$pdo = getDB();

// 1. Check Local CMS First (Hybrid Strategy)
$stmt = $pdo->prepare("SELECT * FROM local_content WHERE tmdb_id = ? AND type = ?");
$stmt->execute([$id, $type]);
$localData = $stmt->fetch();

if ($localData) {
    // USE CMS DATA (Curated)
    $details = [
        'title' => $localData['title'], // Use our custom title
        'name' => $localData['title'],
        'overview' => $localData['overview'],
        'backdrop_path' => $localData['backdrop_path'],
        'vote_average' => $localData['vote_average'],
        'release_date' => $localData['release_date'],
        'first_air_date' => $localData['release_date']
    ];
    // SEO Overrides
    $pageTitle = $localData['seo_title'] ?: $localData['title'];
    $pageDesc = $localData['seo_description'] ?: $localData['overview'];
} else {
    // FALLBACK TO API
    $details = $tmdb->getDetails($id, $type);
    if (!$details) { exit("<p>Content not found.</p>"); }
    $pageTitle = $details['title'] ?? $details['name'];
    $pageDesc = $details['overview'];
}

// ... Rest of the logic ...
$title = $details['title'] ?? $details['name'];
$date = $details['release_date'] ?? $details['first_air_date'] ?? '';
$backdrop = $details['backdrop_path'];
$rating = $details['vote_average'];

// Handle Series Logic (API only for seasons currently, unless fully curated)
$episodes = [];
$total_seasons = 0;
if ($type === 'tv' || $type === 'series') {
    $type = 'tv';
    $total_seasons = $details['number_of_seasons'] ?? 1; // Note: Local DB needs season count column if we purely go local
    // For now, even if local, we can fetch Season Details from TMDB to keep it simple, 
    // BUT we use the Title/Desc from Local DB.
    if (!isset($details['seasons'])) {
         // Re-fetch logic or just trust TMDB for structure
         $apiDetails = $tmdb->getDetails($id, $type);
         $total_seasons = $apiDetails['number_of_seasons'] ?? 1;
    }
    
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

<!-- Dynamic SEO Tags (Javascript Injection or Head Output) -->
<script>
    document.title = "<?php echo htmlspecialchars($pageTitle . ' - Great10'); ?>";
    // Update Meta Desc
    let metaDesc = document.querySelector('meta[name="description"]');
    if (!metaDesc) {
        metaDesc = document.createElement('meta');
        metaDesc.name = "description";
        document.head.appendChild(metaDesc);
    }
    metaDesc.content = "<?php echo htmlspecialchars(substr($pageDesc, 0, 160)); ?>";
    </script>
<?php
// Construct JSON-LD
$schemaType = ($type === 'tv' || $type === 'series') ? 'TVSeries' : 'Movie';
$schemaImage = "https://image.tmdb.org/t/p/w500" . ($details['poster_path'] ?? '');
$schemaDate = $details['release_date'] ?? $details['first_air_date'] ?? '';
$schemaRating = $details['vote_average'] ?? 0;

$schemaData = [
    "@context" => "https://schema.org",
    "@type" => $schemaType,
    "name" => $title,
    "description" => $pageDesc,
    "image" => $schemaImage,
    "datePublished" => $schemaDate,
    "aggregateRating" => [
        "@type" => "AggregateRating",
        "ratingValue" => $schemaRating,
        "bestRating" => "10",
        "ratingCount" => $details['vote_count'] ?? 1
    ]
];
?>
<script type="application/ld+json">
    <?php echo json_encode($schemaData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>
</script>

<!-- Player Wrapper -->
<div class="player-container">
    <iframe src="<?php echo $playerUrl; ?>" allowfullscreen scrolling="no" frameborder="0"></iframe>
</div>

<!-- Content Info -->
<div class="watch-meta">
    <h1 style="font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 800; margin-bottom: 10px; text-shadow: 0 0 20px rgba(255,255,255,0.1);">
        <?php echo htmlspecialchars($title); ?> 
        <?php if($localData): ?><span style="font-size: 0.5em; background: #333; padding: 2px 6px; border-radius: 4px; vertical-align: middle;">CMS</span><?php endif; ?>
    </h1>
    
    <div style="color: #a3a3a3; font-size: 0.95rem; margin-bottom: 25px; display: flex; gap: 15px; align-items: center;">
        <span><?php echo substr($date, 0, 4); ?></span>
        <span style="border: 1px solid rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; background: rgba(255,255,255,0.05);"><?php echo strtoupper($type); ?></span>
        <span style="color: var(--accent-color); font-weight: bold;">★ <?php echo number_format($rating, 1); ?></span>
        
        <!-- My List Button -->
        <button id="watchlistBtn" class="btn btn-secondary" onclick="toggleWatchlist()" style="padding: 5px 15px; font-size: 0.8rem;">
            <span id="watchlistIcon">+</span> My List
        </button>
    </div>

    <!-- Continue Watching Tracker -->
    <!-- Continue Watching & Watchlist Logic -->
    <script>
        // Safely pass PHP variables to JS
        const TMDB_ID = <?php echo json_encode((int)$id); ?>;
        const CONTENT_TYPE = <?php echo json_encode($type); ?>;
        const TITLE = <?php echo json_encode($title); ?>;
        const POSTER = <?php echo json_encode($details['poster_path'] ?? ''); ?>;
        
        // 1. Save Progress (Continue Watching)
        function saveProgress() {
            try {
                let history = JSON.parse(localStorage.getItem('continue_watching') || '[]');
                // Remove existing entry for this item to avoid duplicates
                history = history.filter(item => !(item.id === TMDB_ID && item.type === CONTENT_TYPE));
                // Add to top
                history.unshift({
                    id: TMDB_ID,
                    type: CONTENT_TYPE,
                    title: TITLE,
                    poster: POSTER,
                    timestamp: Date.now()
                });
                // Keep max 20
                if (history.length > 20) history = history.slice(0, 20);
                localStorage.setItem('continue_watching', JSON.stringify(history));
                console.log('Progress Saved:', TITLE);
            } catch (e) {
                console.error('Save Progress Error:', e);
            }
        }
        
        // Execute Save
        if (TMDB_ID && TITLE) {
            saveProgress();
        }

        // 2. Watchlist Logic
        async function checkWatchlist() {
            // Only check if user is logged in (frontend guess, backend validates)
            try {
                const res = await fetch('api/watchlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'check', tmdb_id: TMDB_ID, type: CONTENT_TYPE })
                });
                const data = await res.json();
                if (data.success) {
                    updateWatchlistUI(data.in_watchlist);
                }
            } catch (e) { console.error('Watchlist Check Error:', e); }
        }

        async function toggleWatchlist() {
            const btn = document.getElementById('watchlistBtn');
            const isAdded = btn.getAttribute('data-added') === 'true';
            const action = isAdded ? 'remove' : 'add';

            // Optimistic UI Update
            updateWatchlistUI(!isAdded);

            try {
                const res = await fetch('api/watchlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action, tmdb_id: TMDB_ID, type: CONTENT_TYPE })
                });
                const data = await res.json();
                
                if (!data.success) {
                    // Revert if failed
                    updateWatchlistUI(isAdded);
                    if (data.message === 'Login required') {
                        alert('Please login to use My List');
                        window.location.href = 'index.php?page=login&redirect=' + encodeURIComponent(window.location.href);
                    } else {
                        alert('Error: ' + data.message);
                    }
                }
            } catch (e) { 
                console.error(e);
                updateWatchlistUI(isAdded); // Revert
            }
        }

        function updateWatchlistUI(added) {
            const btn = document.getElementById('watchlistBtn');
            const icon = document.getElementById('watchlistIcon');
            if (!btn) return;

            btn.setAttribute('data-added', added);
            if (added) {
                btn.style.background = 'var(--accent-color)';
                btn.style.borderColor = 'var(--accent-color)';
                btn.innerHTML = '<span id="watchlistIcon">✓</span> In My List';
            } else {
                btn.style.background = 'rgba(255,255,255,0.2)';
                btn.style.borderColor = 'rgba(255,255,255,0.1)';
                btn.innerHTML = '<span id="watchlistIcon">+</span> My List';
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', checkWatchlist);
    </script>

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

    <!-- Comments Section (From V2) -->
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
            <div style="text-align: center; color: #666; padding: 20px;">Loading comments...</div>
        </div>
    </div>
</div>
