<?php
// home.php
// Assumes $pdo is available from index.php

// Fetch Featured Movie (Random or Latest)
$featured = $pdo->query("SELECT * FROM content WHERE type='movie' ORDER BY RANDOM() LIMIT 1")->fetch();

// Fetch Latest Movies
$latestMovies = $pdo->query("SELECT * FROM content WHERE type='movie' ORDER BY id DESC LIMIT 12")->fetchAll();

// Fetch Top Rated (using rating field)
$topRated = $pdo->query("SELECT * FROM content WHERE type='movie' ORDER BY rating DESC LIMIT 12")->fetchAll();
?>

<?php if ($featured): ?>
    <section class="hero" style="background-image: url('https://image.tmdb.org/t/p/original<?php echo $featured['backdrop_path']; ?>');">
        <div class="hero-content">
            <h1><?php echo htmlspecialchars($featured['title']); ?></h1>
            <p style="font-size: 1.2rem; margin-bottom: 20px; text-shadow: 2px 2px 4px #000;"><?php echo htmlspecialchars(substr($featured['overview'], 0, 150)) . '...'; ?></p>
            <div class="buttons">
                <a href="index.php?page=watch&id=<?php echo $featured['id']; ?>" class="btn" style="background: white; color: black; border-color: white;">Play Now</a>
                <a href="index.php?page=watch&id=<?php echo $featured['id']; ?>" class="btn">More Info</a>
            </div>
        </div>
    </section>
<?php else: ?>
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Great10</h1>
            <p>No content available. Please login to Admin Panel to import movies.</p>
            <a href="admin/login.php" class="btn">Go to Admin</a>
        </div>
    </section>
<?php endif; ?>

<section class="section">
    <div class="section-title">Latest Movies</div>
    <div class="media-grid">
        <?php foreach ($latestMovies as $item): ?>
            <a href="index.php?page=watch&id=<?php echo $item['id']; ?>" class="media-card">
                <img src="https://image.tmdb.org/t/p/w500<?php echo $item['poster_path']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                <div class="info">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <span><?php echo substr($item['release_date'], 0, 4); ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="section-title">Top Rated</div>
    <div class="media-grid">
        <?php foreach ($topRated as $item): ?>
            <a href="index.php?page=watch&id=<?php echo $item['id']; ?>" class="media-card">
                <img src="https://image.tmdb.org/t/p/w500<?php echo $item['poster_path']; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                <div class="info">
                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                    <span>★ <?php echo $item['rating']; ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
