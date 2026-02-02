<?php
// category.php changed
require_once 'api/config.php';
require_once 'api/tmdb.php';

$genreId = $_GET['id'] ?? 0;
$genreName = $_GET['name'] ?? 'Genre';
$type = $_GET['type'] ?? 'movie';

// Initial Load (Page 1) server-side for SEO
$tmdb = new TMDB();
$endpoint = ($type === 'tv') ? '/discover/tv' : '/discover/movie';
$initialData = $tmdb->request($endpoint, ['with_genres' => $genreId, 'page' => 1]);
$initialResults = $initialData['results'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($genreName); ?> - Great10</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
    <style>
        .header { background: #141414; } /* Always solid header */
        .category-header { padding: 120px 40px 20px; text-align: center; }
        .category-header h1 { font-size: 3rem; margin-bottom: 10px; }
        .loading-spinner { text-align: center; padding: 40px; display: none; }
    </style>
</head>
<body>
    <?php
    // Fetch Settings for Header
    $pdo = getDB();
    $settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    $siteName = $settings['site_name'] ?? 'Great10';
    $logo = $settings['site_logo'] ?? '';
    ?>
    <header id="main-header" class="scrolled">
        <a href="index.php" class="logo">
            <?php if ($logo): ?>
                <img src="<?php echo $logo; ?>" alt="<?php echo $siteName; ?>" style="height: 35px; vertical-align: middle;">
            <?php else: ?>
                <?php echo $siteName; ?>
            <?php endif; ?>
        </a>
        <nav>
            <a href="index.php">Home</a>
            <a href="index.php?page=search">Search</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php?page=dashboard">My Account</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin/index.php" target="_blank">Admin</a>
                <?php endif; ?>
                <a href="api/logout.php" onclick="return confirm('Logout?');">Sign Out</a>
            <?php else: ?>
                <a href="index.php?page=login" class="btn btn-primary" style="padding: 5px 15px;">Login</a>
            <?php endif; ?>
        </nav>
        
        <!-- Search Bar (Live) -->
        <form action="index.php" method="GET" style="margin-left: 20px; display: inline-block;">
            <input type="hidden" name="page" value="search">
            <input type="text" name="query" placeholder="Search..." class="search-input" autocomplete="off">
        </form>
    </header>

    <div class="category-header">
        <h1><?php echo htmlspecialchars($genreName); ?></h1>
        <p>Browsing <?php echo ucfirst($type); ?>s</p>
    </div>

    <section class="section">
        <div class="media-grid" id="infinite-grid" data-genre="<?php echo $genreId; ?>" data-type="<?php echo $type; ?>" data-page="1">
            <?php foreach ($initialResults as $item): 
                $img = 'https://image.tmdb.org/t/p/w500' . $item['poster_path'];
                $title = htmlspecialchars($item['title'] ?? $item['name']);
                $year = substr($item['release_date'] ?? $item['first_air_date'] ?? '', 0, 4);
                if (!$item['poster_path']) continue;
            ?>
            <a href="index.php?page=watch&type=<?php echo $type; ?>&id=<?php echo $item['id']; ?>" class="media-card">
                <img src="<?php echo $img; ?>" alt="<?php echo $title; ?>" loading="lazy">
                <div class="info">
                    <h3><?php echo $title; ?></h3>
                    <span><?php echo $year; ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="loading-spinner" id="loading-spinner">
            <svg viewBox="0 0 50 50" style="width: 40px; animation: spin 1s linear infinite;"><circle cx="25" cy="25" r="20" fill="none" stroke="red" stroke-width="5"></circle></svg>
        </div>
    </section>

    <!-- Infinite Scroll Script -->
    <script>
        let page = 1;
        let isLoading = false;
        const grid = document.getElementById('infinite-grid');
        const spinner = document.getElementById('loading-spinner');
        const genreId = grid.dataset.genre;
        const type = grid.dataset.type;

        window.addEventListener('scroll', () => {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
                loadMore();
            }
        });

        async function loadMore() {
            if (isLoading) return;
            isLoading = true;
            spinner.style.display = 'block';
            page++;

            try {
                const res = await fetch(`api/browse.php?genre=${genreId}&type=${type}&page=${page}`);
                const data = await res.json();
                
                if (data.results && data.results.length > 0) {
                    data.results.forEach(item => {
                        const img = 'https://image.tmdb.org/t/p/w500' + item.poster_path;
                        const link = `index.php?page=watch&type=${item.type}&id=${item.id}`;
                        const div = document.createElement('a');
                        div.className = 'media-card';
                        div.href = link;
                        div.innerHTML = `
                            <img src="${img}" alt="${item.title}" loading="lazy">
                            <div class="info">
                                <h3>${item.title}</h3>
                                <span>${item.year}</span>
                            </div>
                        `;
                        grid.appendChild(div);
                    });
                } else {
                    // No more results
                    window.removeEventListener('scroll', loadMore);
                }
            } catch (e) { console.error(e); }
            
            isLoading = false;
            spinner.style.display = 'none';
        }
    </script>
</body>
</html>
