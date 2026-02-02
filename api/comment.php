<?php
// api/comment.php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    $tmdbId = $_POST['tmdb_id'] ?? 0;
    $type = $_POST['type'] ?? 'movie';
    $comment = trim($_POST['comment']);

    if (!$comment) {
        http_response_code(400);
        echo json_encode(['error' => 'Empty comment']);
        exit;
    }

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, tmdb_id, type, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $tmdbId, $type, $comment]);
        
        // Fetch username if not in session
        $username = $_SESSION['username'] ?? 'User';
        if ($username === 'User') {
            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $u = $stmt->fetch();
            if ($u) $username = $u['username'];
        }

        echo json_encode(['success' => true, 'username' => $username, 'date' => date('Y-m-d H:i')]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $tmdbId = $_GET['tmdb_id'] ?? 0;
    
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT c.*, u.username 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.tmdb_id = ? 
            ORDER BY c.created_at DESC 
            LIMIT 50
        ");
        $stmt->execute([$tmdbId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($comments);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
