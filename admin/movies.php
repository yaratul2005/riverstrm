<?php
// admin/movies.php
require_once '../api/config.php';
require_once '../api/tmdb.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$tmdb = new TMDB();
$message = '';
$searchResults = [];

// Handle Import
if (isset($_POST['import_id'])) {
    $tmdbId = $_POST['import_id'];
    $details = $tmdb->getDetails($tmdbId, 'movie');
    
    if ($details) {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO content (tmdb_id, title, overview, poster_path, backdrop_path, type, release_date, rating) VALUES (?, ?, ?, ?, ?, 'movie', ?, ?)");
        try {
            $stmt->execute([
                $details['id'],
                $details['title'],
                $details['overview'],
                $details['poster_path'],
                $details['backdrop_path'],
                $details['release_date'],
                $details['vote_average']
            ]);
            $message = "Imported: " . htmlspecialchars($details['title']);
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Handle Search
if (isset($_GET['query'])) {
    $results = $tmdb->search($_GET['query'], 'movie');
    $searchResults = $results['results'] ?? [];
}

// List Existing
$pdo = getDB();
$movies = $pdo->query("SELECT * FROM content WHERE type='movie' ORDER BY id DESC LIMIT 50")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Movies</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; height: 100vh; }
        .sidebar { width: 250px; background: #111; padding: 20px; border-right: 1px solid #333; }
        .sidebar a { display: block; padding: 10px; color: #888; margin-bottom: 5px; }
        .sidebar a:hover { color: white; background: #222; }
        .admin-content { flex: 1; padding: 40px; overflow-y: auto; }
        
        .search-box { display: flex; gap: 10px; margin-bottom: 30px; }
        .search-box input { flex: 1; padding: 10px; background: #222; color: white; border: 1px solid #444; }
        
        .result-item { display: flex; align-items: center; background: #111; margin-bottom: 10px; padding: 10px; border: 1px solid #333; }
        .result-item img { width: 50px; height: 75px; object-fit: cover; margin-right: 20px; }
        .result-item .info { flex: 1; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #333; }
        th { background: #222; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <div class="sidebar">
            <h2 class="logo" style="margin-bottom: 30px;">Admin</h2>
            <a href="index.php">Dashboard</a>
            <a href="movies.php" style="color: white; background: #222;">Manage Movies</a>
            <a href="series.php">Manage Series</a>
            <a href="../index.php">View Site</a>
        </div>
        <div class="admin-content">
            <h1>Manage Movies</h1>
            
            <?php if ($message): ?>
                <div style="background: green; padding: 10px; margin-bottom: 20px;"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Import Section -->
            <div class="section-title">Import from TMDB</div>
            <form method="GET" class="search-box">
                <input type="text" name="query" placeholder="Search for movies to import..." value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>">
                <button type="submit" class="btn">Search</button>
            </form>

            <?php if (!empty($searchResults)): ?>
                <div class="results">
                    <?php foreach ($searchResults as $movie): ?>
                        <div class="result-item">
                            <img src="<?php echo $tmdb->getImageUrl($movie['poster_path'], 'w92'); ?>" alt="poster">
                            <div class="info">
                                <h3><?php echo htmlspecialchars($movie['title']); ?> (<?php echo substr($movie['release_date'] ?? '', 0, 4); ?>)</h3>
                                <p><?php echo substr($movie['overview'], 0, 100); ?>...</p>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="import_id" value="<?php echo $movie['id']; ?>">
                                <button type="submit" class="btn">Import</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <hr style="border-color: #333; margin: 40px 0;">

            <!-- List Section -->
             <div class="section-title">Existing Library</div>
             <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Year</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movies as $movie): ?>
                        <tr>
                            <td><?php echo $movie['id']; ?></td>
                            <td><?php echo htmlspecialchars($movie['title']); ?></td>
                            <td><?php echo substr($movie['release_date'], 0, 4); ?></td>
                            <td><a href="#" style="color: red;">Delete</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
             </table>
        </div>
    </div>
</body>
</html>
