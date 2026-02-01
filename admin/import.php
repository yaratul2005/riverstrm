<?php
// admin/import.php
session_start();
require_once '../api/config.php';
require_once '../api/tmdb.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$pdo = getDB();
$tmdb = new TMDB();
$search_results = [];
$success_msg = '';
$error_msg = '';

// Handle Import
if (isset($_POST['import_id'])) {
    $id = $_POST['import_id'];
    $type = $_POST['import_type'];
    
    try {
        $data = $tmdb->getDetails($id, $type);
        if ($data) {
            $title = $data['title'] ?? $data['name'];
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            $date = $data['release_date'] ?? $data['first_air_date'];
            
            $stmt = $pdo->prepare("INSERT INTO local_content (tmdb_id, type, title, slug, overview, poster_path, backdrop_path, release_date, vote_average, seo_title, seo_description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=VALUES(title), overview=VALUES(overview)");
            
            $stmt->execute([
                $id, $type, $title, $slug . '-' . $id, // ID to ensure uniqueness
                $data['overview'], $data['poster_path'], $data['backdrop_path'],
                $date, $data['vote_average'],
                "Watch $title Online Free - Great10",
                "Stream $title in HD on Great10. " . substr($data['overview'], 0, 100) . "..."
            ]);
            $success_msg = "Imported: $title";
        }
    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Handle Search
if (isset($_GET['query'])) {
    $results = $tmdb->request('/search/multi', ['query' => $_GET['query']]);
    $search_results = $results['results'] ?? [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Import Content - CMS</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .result-item { display: flex; gap: 15px; background: #222; padding: 10px; margin-bottom: 10px; border-radius: 6px; align-items: center; }
        .result-item img { width: 50px; border-radius: 4px; }
        .import-btn { padding: 5px 15px; background: var(--accent-color); color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div style="max-width: 800px; margin: 50px auto; padding: 0 20px;">
        <h1 style="border-bottom: 1px solid #333; padding-bottom: 20px;">Curator Import</h1>
        <a href="index.php" style="color: #ccc; margin-right: 20px;">&larr; Dashboard</a>
        <a href="manage_content.php" style="color: #ccc;">Manage Content &rarr;</a>
        
        <?php if ($success_msg): ?><p style="color: green; margin: 20px 0;"><?php echo $success_msg; ?></p><?php endif; ?>
        
        <form style="margin: 30px 0; display: flex; gap: 10px;">
            <input type="text" name="query" placeholder="Search TMDB..." value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>" style="flex: 1; padding: 10px; background: #111; border: 1px solid #333; color: white;">
            <button class="btn btn-primary">Search</button>
        </form>

        <div class="results">
            <?php foreach ($search_results as $item): 
                if (($item['media_type'] ?? '') == 'person') continue;
                $title = $item['title'] ?? $item['name'];
                $img = $item['poster_path'] ? "https://image.tmdb.org/t/p/w92" . $item['poster_path'] : "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
            ?>
            <div class="result-item">
                <img src="<?php echo $img; ?>">
                <div style="flex: 1;">
                    <strong><?php echo htmlspecialchars($title); ?></strong>
                    <span style="color: #888; font-size: 0.8rem; margin-left: 10px; border: 1px solid #444; padding: 2px 4px;"><?php echo strtoupper($item['media_type'] ?? 'unk'); ?></span>
                </div>
                <form method="POST">
                    <input type="hidden" name="import_id" value="<?php echo $item['id']; ?>">
                    <input type="hidden" name="import_type" value="<?php echo $item['media_type'] ?? 'movie'; ?>">
                    <button class="import-btn">Import</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
