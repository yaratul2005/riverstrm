<?php
// admin/users.php
// session_start();
require_once '../api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$pdo = getDB();
$message = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $message = "User deleted.";
}

// Fetch Users
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; height: 100vh; }
        .sidebar { width: 250px; background: #111; padding: 20px; border-right: 1px solid #333; }
        .sidebar a { display: block; padding: 10px; color: #888; margin-bottom: 5px; }
        .sidebar a:hover { color: white; background: #222; }
        .admin-content { flex: 1; padding: 40px; overflow-y: auto; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 10px; border-bottom: 1px solid #333; }
        th { background: #222; }
        .btn-delete { color: #f44336; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <div class="sidebar">
            <h2 class="logo" style="margin-bottom: 30px;">Admin</h2>
            <a href="index.php">Dashboard</a>
            <a href="pages.php">Pages (CMS)</a>
            <a href="users.php" style="color: white; background: #222;">Users</a>
            <a href="manage_content.php">Content</a>
            <a href="import.php">Curator</a>
            <a href="diagnostics.php">Diagnostics</a>
            <a href="settings.php">Settings</a>
            <a href="../index.php">View Site</a>
        </div>
        <div class="admin-content">
            <h1>Manage Users</h1>
            <?php if ($message): ?><p style="color: green;"><?php echo $message; ?></p><?php endif; ?>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo $u['role']; ?></td>
                        <td><?php echo $u['created_at']; ?></td>
                        <td>
                            <?php if ($u['role'] !== 'admin'): ?>
                                <a href="?delete=<?php echo $u['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                            <?php else: ?>
                                <span style="color: #666;">Protected</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
