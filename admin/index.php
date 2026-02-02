<?php
// admin/index.php
// session_start(); // Handled in config.php
require_once '../api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$pdo = getDB();

// Fetch Stats
$stats = [
    'users'     => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'movies'    => $pdo->query("SELECT COUNT(*) FROM local_content WHERE type='movie'")->fetchColumn(),
    'series'    => $pdo->query("SELECT COUNT(*) FROM local_content WHERE type='tv'")->fetchColumn(),
    'comments'  => $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
];

$recent_users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recent_content = $pdo->query("SELECT * FROM local_content ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Great10 CMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #050505; color: white; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 250px; background: #111; border-right: 1px solid #222; padding: 20px; display: flex; flex-direction: column; position: fixed; height: 100%; }
        .sidebar .brand { font-size: 1.5rem; font-weight: 900; color: var(--accent-color); margin-bottom: 40px; }
        .sidebar a { display: block; padding: 12px 15px; color: #888; border-radius: 6px; margin-bottom: 5px; transition: 0.2s; }
        .sidebar a:hover, .sidebar a.active { background: #222; color: white; }
        .sidebar .logout { margin-top: auto; color: #d9534f; }

        /* Main Content */
        .main { margin-left: 250px; flex: 1; padding: 40px; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h1 { font-size: 2rem; }
        .header .user-badge { background: #222; padding: 8px 15px; border-radius: 20px; font-size: 0.9rem; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: #161616; padding: 25px; border-radius: 12px; border: 1px solid #222; position: relative; overflow: hidden; }
        .stat-card h3 { font-size: 2.5rem; margin-bottom: 5px; }
        .stat-card p { color: #888; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card::after { content: ''; position: absolute; top: -50%; right: -50%; width: 100px; height: 100px; background: var(--accent-color); opacity: 0.1; filter: blur(40px); border-radius: 50%; }

        /* Actions Grid */
        .actions-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-bottom: 40px; }
        .action-btn { background: #222; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #333; transition: 0.2s; display: block; }
        .action-btn:hover { background: var(--accent-color); border-color: var(--accent-color); transform: translateY(-3px); }
        .action-btn div { font-size: 1.5rem; margin-bottom: 10px; }

        /* Tables */
        .table-section { background: #161616; padding: 25px; border-radius: 12px; border: 1px solid #222; margin-bottom: 30px; }
        .table-section h2 { margin-bottom: 20px; font-size: 1.2rem; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: #888; font-weight: 500; padding-bottom: 15px; border-bottom: 1px solid #333; }
        td { padding: 15px 0; border-bottom: 1px solid #222; color: #ddd; }
        tr:last-child td { border-bottom: none; }

        @media (max-width: 768px) {
            .sidebar { display: none; }
            .main { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="brand">Great10 CMS</div>
        <a href="index.php" class="active">Dashboard</a>
        <a href="pages.php">Pages (CMS)</a>
        <a href="users.php">Users</a>
        <a href="manage_content.php">Content</a>
        <a href="import.php">Curator</a>
        <a href="diagnostics.php">Diagnostics</a>
        <a href="settings.php">Settings</a>
        <a href="../index.php" target="_blank">View Site &rarr;</a>
        <a href="../api/logout.php" class="logout">Logout</a>
    </div>

    <div class="main">
        <div class="header">
            <h1>Overview</h1>
            <div class="user-badge">Admin</div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo number_format($stats['users']); ?></h3>
                <p>Total Users</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($stats['movies']); ?></h3>
                <p>Curated Movies</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($stats['series']); ?></h3>
                <p>Curated Series</p>
            </div>
            <div class="stat-card">
                <h3><?php echo number_format($stats['comments']); ?></h3>
                <p>Comments</p>
            </div>
        </div>

        <h2 style="margin-bottom: 15px;">Quick Actions</h2>
        <div class="actions-grid">
            <a href="import.php" class="action-btn">
                <div>📥</div>
                <span>Import Content</span>
            </a>
            <a href="manage_content.php" class="action-btn">
                <div>✏️</div>
                <span>Manage CMS</span>
            </a>
            <a href="settings.php" class="action-btn">
                <div>⚙️</div>
                <span>Site Settings</span>
            </a>
            <a href="diagnostics.php" class="action-btn">
                <div>❤️</div>
                <span>Health Check</span>
            <a href="diagnostics.php" class="action-btn">
                <div>❤️</div>
                <span>Health Check</span>
            </a>
            <a href="sitemap_tool.php" class="action-btn">
                <div>🗺️</div>
                <span>Sitemap Tool</span>
            </a>
        </div>

        <div class="table-section">
            <h2>Recent Curated Content</h2>
            <?php if (empty($recent_content)): ?>
                <p style="color: #666;">No content imported yet.</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_content as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td><span style="background: #333; padding: 2px 6px; border-radius: 4px; font-size: 0.8rem;"><?php echo strtoupper($item['type']); ?></span></td>
                        <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div class="table-section">
            <h2>New Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
