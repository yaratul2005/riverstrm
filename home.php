<?php
// home.php
require_once 'api/tmdb.php';

$tmdb = new TMDB();

// Fetch Data Live from TMDB
$trending = $tmdb->getTrending('movie', 'week')['results'];
$featured = $trending[0]; // Top trending item as Hero

$popularMovies = $tmdb->getPopular('movie')['results'];
$topRatedMovies = $tmdb->getTopRated('movie')['results'];
$popularSeries = $tmdb->getPopular('tv')['results'];
?>

<!-- Hero Section -->
<section class="hero" style="background-image: url('https://image.tmdb.org/t/p/original<?php echo $featured['backdrop_path']; ?>');">
    <div class="hero-content">
        <h1><?php echo htmlspecialchars($featured['title'] ?? $featured['name']); ?></h1>
        <p style="font-size: 1.2rem; margin-bottom: 20px; text-shadow: 2px 2px 4px #000;"><?php echo htmlspecialchars(substr($featured['overview'], 0, 150)) . '...'; ?></p>
        <div class="buttons">
            <a href="index.php?page=watch&type=movie&id=<?php echo $featured['id']; ?>" class="btn" style="background: white; color: black; border-color: white;">Play Now</a>
            <a href="index.php?page=watch&type=movie&id=<?php echo $featured['id']; ?>" class="btn">More Info</a>
        </div>
    </div>
</section>

<!-- Trending Section -->
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
