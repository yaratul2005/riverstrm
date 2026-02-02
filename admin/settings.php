<?php
// admin/settings.php
// session_start();
require_once '../api/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$pdo = getDB();
$message = '';

// Handle Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("REPLACE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    
    $settings = [
        'site_name' => $_POST['site_name'],
        'smtp_host' => $_POST['smtp_host'],
        'smtp_port' => $_POST['smtp_port'],
        'smtp_user' => $_POST['smtp_user'],
        'smtp_pass' => $_POST['smtp_pass'],
        'smtp_from' => $_POST['smtp_from'],
        'google_client_id' => $_POST['google_client_id'],
        'google_client_secret' => $_POST['google_client_secret'],
        'google_redirect_uri' => $_POST['google_redirect_uri'],
    ];

    // Handle File Uploads
    $uploadDir = '../assets/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $logoName = 'logo_' . time() . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoName);
        $settings['site_logo'] = $uploadDir . $logoName;
    }

    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === 0) {
        $favName = 'favicon_' . time() . '.' . pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['favicon']['tmp_name'], $uploadDir . $favName);
        $settings['site_favicon'] = $uploadDir . $favName;
    }

    // Add extra fields
    $settings['head_code'] = $_POST['head_code'];

    foreach ($settings as $key => $val) {
        $stmt->execute([$key, trim($val)]);
    }
    $message = "Settings Saved Successfully!";
}

// Fetch Current Settings
$current = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .setting-group { background: #222; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #ccc; }
        input { width: 100%; padding: 10px; background: #000; border: 1px solid #444; color: white; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div style="max-width: 800px; margin: 50px auto; padding: 0 20px;">
        <h1 style="border-bottom: 1px solid #333; padding-bottom: 20px; margin-bottom: 30px;">Site Settings</h1>
        
        <?php if ($message): ?>
            <div style="background: green; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="setting-group">
                <h3>General</h3>
                <label>Site Name</label>
                <input type="text" name="site_name" value="<?php echo htmlspecialchars($current['site_name'] ?? 'Great10 Streaming'); ?>">
                
                <label>Site Logo</label>
                <?php if (!empty($current['site_logo'])): ?>
                    <img src="<?php echo $current['site_logo']; ?>" height="50" style="margin-bottom: 10px; display: block; background: #333; padding: 5px;">
                <?php endif; ?>
                <input type="file" name="logo" accept="image/*">

                <label>Favicon</label>
                <?php if (!empty($current['site_favicon'])): ?>
                    <img src="<?php echo $current['site_favicon']; ?>" height="32" style="margin-bottom: 10px; display: block;">
                <?php endif; ?>
                <input type="file" name="favicon" accept="image/*">
            </div>

            <div class="setting-group">
                <h3>Custom Code</h3>
                <label>Head HTML (Analytics, Scripts, Verification)</label>
                <textarea name="head_code" rows="6" placeholder="<script>...</script>"><?php echo htmlspecialchars($current['head_code'] ?? ''); ?></textarea>
            </div>

            <div class="setting-group">
                <h3>SMTP Configuration (Email)</h3>
                <label>SMTP Host</label>
                <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($current['smtp_host'] ?? ''); ?>" placeholder="smtp.gmail.com">
                
                <div style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <label>SMTP Port</label>
                        <input type="text" name="smtp_port" value="<?php echo htmlspecialchars($current['smtp_port'] ?? '587'); ?>">
                    </div>
                    <div style="flex: 1;">
                        <label>From Email</label>
                        <input type="email" name="smtp_from" value="<?php echo htmlspecialchars($current['smtp_from'] ?? ''); ?>">
                    </div>
                </div>

                <label>SMTP Username</label>
                <input type="text" name="smtp_user" value="<?php echo htmlspecialchars($current['smtp_user'] ?? ''); ?>">
                
                <label>SMTP Password</label>
                <input type="password" name="smtp_pass" value="<?php echo htmlspecialchars($current['smtp_pass'] ?? ''); ?>">
            </div>

            <div class="setting-group">
                <h3>Google OAuth</h3>
                <label>Client ID</label>
                <input type="text" name="google_client_id" value="<?php echo htmlspecialchars($current['google_client_id'] ?? ''); ?>">
                
                <label>Client Secret</label>
                <input type="password" name="google_client_secret" value="<?php echo htmlspecialchars($current['google_client_secret'] ?? ''); ?>">
                
                <label>Redirect URI</label>
                <input type="text" name="google_redirect_uri" value="<?php echo htmlspecialchars($current['google_redirect_uri'] ?? 'https://great10.xyz/api/google_login.php'); ?>">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Save Settings</button>
            <a href="index.php" style="display: block; text-align: center; margin-top: 20px; color: #888;">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
