<?php
// api/browse.php
require_once 'config.php';
require_once 'tmdb.php';

header('Content-Type: application/json');

$tmdb = new TMDB();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$type = isset($_GET['type']) ? $_GET['type'] : 'movie'; // movie, tv, multi
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$genre = isset($_GET['genre']) ? (int)$_GET['genre'] : 0;

$results = [];

try {
    if (!empty($query)) {
        // Search Mode
        // 'multi' searches both movies and TV shows
        $data = $tmdb->search($query, 'multi', $page);
        $results = $data['results'] ?? [];
    } elseif ($genre > 0) {
        // Genre Browse Mode
        $endpoint = ($type === 'tv') ? 'discover/tv' : 'discover/movie';
        $params = [
            'with_genres' => $genre,
            'page' => $page,
            'sort_by' => 'popularity.desc'
        ];
        $data = $tmdb->get($endpoint, $params);
        $results = $data['results'] ?? [];
    }

    // Filter results to remove items without posters (cleaner UI)
    $cleanResults = [];
    foreach ($results as $item) {
        if (!empty($item['poster_path'])) {
            $cleanResults[] = [
                'id' => $item['id'],
                'tmdb_id' => $item['id'],
                'title' => $item['title'] ?? $item['name'] ?? 'Unknown',
                'poster_path' => $item['poster_path'],
                'type' => $item['media_type'] ?? $type,
                'year' => isset($item['release_date']) ? substr($item['release_date'], 0, 4) : (isset($item['first_air_date']) ? substr($item['first_air_date'], 0, 4) : '')
            ];
        }
    }

    echo json_encode(['success' => true, 'results' => $cleanResults, 'page' => $page]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
