<?php
// api/watchlist.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$pdo = getDB();
$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$action = $data['action'] ?? '';
$tmdbId = $data['tmdb_id'] ?? 0;
$type = $data['type'] ?? 'movie';

if (!$tmdbId) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

try {
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT IGNORE INTO watchlist (user_id, tmdb_id, type) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $tmdbId, $type]);
        echo json_encode(['success' => true, 'status' => 'added']);
    } elseif ($action === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND tmdb_id = ? AND type = ?");
        $stmt->execute([$userId, $tmdbId, $type]);
        echo json_encode(['success' => true, 'status' => 'removed']);
    } elseif ($action === 'check') {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM watchlist WHERE user_id = ? AND tmdb_id = ? AND type = ?");
        $stmt->execute([$userId, $tmdbId, $type]);
        $exists = $stmt->fetchColumn() > 0;
        echo json_encode(['success' => true, 'in_watchlist' => $exists]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
