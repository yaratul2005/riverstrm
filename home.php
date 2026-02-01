<?php
// home.php
require_once 'api/tmdb.php';

$tmdb = new TMDB();

// Fetch Data Live from TMDB
$trending = $tmdb->getTrending('movie', 'week')['results'];
$slides = array_slice($trending, 0, 5); // Top 5 for slider

$popularMovies = $tmdb->getPopular('movie')['results'];
$popularSeries = $tmdb->getPopular('tv')['results'];
$topRatedMovies = $tmdb->getTopRated('movie')['results'];

// Categories (Static for now, could be dynamic)
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
        $title = $slide['title'] ?? $slide['name'];
        $desc = substr($slide['overview'], 0, 150) . '...';
    ?>
    <div class="hero-slide <?php echo $activeClass; ?>">
        <div class="hero-bg" style="background-image: url('<?php echo $bgImage; ?>');"></div>
        <div class="hero-content" style="position: absolute; bottom: 20%; left: 5%; z-index: 10;">
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <p><?php echo htmlspecialchars($desc); ?></p>
            <div class="buttons">
                <a href="index.php?page=watch&type=movie&id=<?php echo $slide['id']; ?>" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" style="width:24px;height:24px;fill:currentColor;"><path d="M8 5v14l11-7z"/></svg>
                    Play Now
                </a>
                <a href="index.php?page=watch&type=movie&id=<?php echo $slide['id']; ?>" class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" style="width:24px;height:24px;fill:currentColor;"><path d="M11 7h2v2h-2zm0 4h2v6h-2zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                    More Info
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

<!-- Trending Section (Grid) -->
<section class="section">
    <div class="section-title">Trending Now</div>
    <div class="media-grid">
        <?php foreach ($trending as $item): 
            $title = $item['title'] ?? $item['name'];
            $date = $item['release_date'] ?? $item['first_air_date'];
            $type = isset($item['title']) ? 'movie' : 'tv';
        ?>
            <a href="index.php?page=watch&type=<?php echo $type; ?>&id=<?php echo $item['id']; ?>" class="media-card">
                <img src="https://image.tmdb.org/t/p/w500<?php echo $item['poster_path']; ?>" alt="<?php echo htmlspecialchars($title); ?>" loading="lazy">
                <div class="info">
                    <h3><?php echo htmlspecialchars($title); ?></h3>
                    <span><?php echo substr($date, 0, 4); ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Popular Movies -->
<section class="section">
    <div class="section-title">Popular Movies</div>
    <div class="media-grid">
        <?php foreach ($popularMovies as $item): ?>
            <a href="index.php?page=watch&type=movie&id=<?php echo $item['id']; ?>" class="media-card">
                <img src="https://image.tmdb.org/t/p/w500<?php echo $item['poster_path']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" loading="lazy">
                <div class="info">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <span><?php echo substr($item['release_date'], 0, 4); ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Popular TV Series -->
<section class="section">
    <div class="section-title">Popular Series</div>
    <div class="media-grid">
        <?php foreach ($popularSeries as $item): ?>
            <a href="index.php?page=watch&type=tv&id=<?php echo $item['id']; ?>" class="media-card">
                <img src="https://image.tmdb.org/t/p/w500<?php echo $item['poster_path']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" loading="lazy">
                <div class="info">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <span><?php echo substr($item['first_air_date'], 0, 4); ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
