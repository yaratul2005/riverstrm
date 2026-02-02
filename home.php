<?php
// home.php
require_once 'api/tmdb.php';

$pdo = getDB();
$tmdb = new TMDB();

// 1. Fetch Featured Content (Local CMS)
$slides = $pdo->query("SELECT * FROM local_content WHERE is_featured = 1 AND is_active = 1 LIMIT 5")->fetchAll();

// Fallback to TMDB Trending if no local featured content
if (count($slides) < 3) {
    $trending = $tmdb->getTrending('movie', 'week')['results'];
    // Merge: Prefer local slides, append TMDB results
    foreach($trending as $t) {
        if (count($slides) >= 5) break; 
        // Convert to compatible format
        $slides[] = [
            'id' => $t['id'], // Note: This ID logic in links needs care
            'tmdb_id' => $t['id'],
            'type' => 'movie',
            'title' => $t['title'],
            'overview' => $t['overview'],
            'backdrop_path' => $t['backdrop_path'],
            'is_local' => false
        ];
    }
}

// 2. Fetch Other Sections (API)
$popularMovies = $tmdb->getPopular('movie')['results'];
$popularSeries = $tmdb->getPopular('tv')['results'];

// Categories (Static)
$categories = [
    ['id' => 28, 'name' => 'Action'],
    ['id' => 12, 'name' => 'Adventure'],
    ['id' => 16, 'name' => 'Animation'],
    ['id' => 35, 'name' => 'Comedy'],
    ['id' => 80, 'name' => 'Crime'],
    ['id' => 18, 'name' => 'Drama'],
    ['id' => 27, 'name' => 'Horror'],
    ['id' => 10765, 'name' => 'Sci-Fi & Fantasy'],
];
?>

<!-- Hero Slider -->
<section class="hero">
    <?php foreach ($slides as $index => $slide): 
        $activeClass = $index === 0 ? 'active' : '';
        $bgImage = "https://image.tmdb.org/t/p/original" . $slide['backdrop_path'];
        $title = $slide['title'];
        $desc = substr($slide['overview'], 0, 150) . '...';
        
        // Link Logic: Local content uses slug if available, else standard ID
        if (isset($slide['is_local']) && $slide['is_local'] === false) {
            $link = "index.php?page=watch&type=movie&id=" . $slide['id'];
        } else {
            // Local Data
            $type = $slide['type'];
            $id = $slide['tmdb_id'];
            $link = "index.php?page=watch&type=$type&id=$id";
        }
    ?>
    <div class="hero-slide <?php echo $activeClass; ?>">
        <div class="hero-bg" style="background-image: url('<?php echo $bgImage; ?>');"></div>
        <div class="hero-content" style="position: absolute; bottom: 20%; left: 5%; z-index: 10;">
            <?php if (!isset($slide['is_local'])): ?>
                <span style="background: var(--accent-color); color: white; padding: 2px 8px; font-size: 0.8rem; border-radius: 4px; margin-bottom: 10px; display: inline-block;">FEATURED</span>
            <?php endif; ?>
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <p><?php echo htmlspecialchars($desc); ?></p>
            <div class="buttons">
                <a href="<?php echo $link; ?>" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" style="width:24px;height:24px;fill:currentColor;"><path d="M8 5v14l11-7z"/></svg>
                    Play Now
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</section>

<!-- Categories -->
<div class="category-bar">
    <?php foreach ($categories as $cat): ?>
        <a href="index.php?page=search&genre=<?php echo $cat['id']; ?>" class="cat-chip"><?php echo $cat['name']; ?></a>
    <?php endforeach; ?>
</div>

<!-- Curated / Recent Section (Local DB) -->
<?php 
$localRecent = $pdo->query("SELECT * FROM local_content WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6")->fetchAll();
if (!empty($localRecent)):
?>
<section class="section">
    <div class="section-title">Fresh on Great10 (Curated)</div>
    <div class="media-grid">
        <?php foreach ($localRecent as $item): 
            $img = "https://image.tmdb.org/t/p/w500" . $item['poster_path'];
        ?>
            <a href="index.php?page=watch&type=<?php echo $item['type']; ?>&id=<?php echo $item['tmdb_id']; ?>" class="media-card">
                <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" loading="lazy">
                <div class="info">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <span>2023</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Continue Watching (Client-Side) -->
<section class="section" id="continue-watching-section" style="display: none;">
    <div class="section-title">Continue Watching</div>
    <div class="media-grid" id="continue-watching-grid"></div>
</section>
<script>
    const history = JSON.parse(localStorage.getItem('continue_watching') || '[]');
    if (history.length > 0) {
        document.getElementById('continue-watching-section').style.display = 'block';
        const grid = document.getElementById('continue-watching-grid');
        
        history.forEach(item => {
            const img = item.poster ? 'https://image.tmdb.org/t/p/w300' + item.poster : 'https://via.placeholder.com/300x450?text=No+Poster';
            const link = `index.php?page=watch&type=${item.type}&id=${item.id}`;
            const html = `
                <a href="${link}" class="media-card">
                    <img src="${img}" alt="${item.title}" loading="lazy">
                    <div class="info">
                        <h3>${item.title}</h3>
                        <span>Resume</span>
                    </div>
                </a>
            `;
            grid.innerHTML += html;
        });
    }
</script>

<!-- Popular Movies (API) -->
<section class="section">
    <div class="section-title">Popular Movies</div>
    <div class="media-grid">
        <?php foreach ($popularMovies as $item): ?>
            <a href="index.php?page=watch&type=movie&id=<?php echo $item['id']; ?>" class="media-card">
                <img src="https://image.tmdb.org/t/p/w500<?php echo $item['poster_path']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" loading="lazy">
                <div class="info">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <span>CMS API</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Popular Series (API) -->
<section class="section">
    <div class="section-title">Popular Series</div>
    <div class="media-grid">
        <?php foreach ($popularSeries as $item): ?>
            <a href="index.php?page=watch&type=tv&id=<?php echo $item['id']; ?>" class="media-card">
                <img src="https://image.tmdb.org/t/p/w500<?php echo $item['poster_path']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" loading="lazy">
                <div class="info">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
