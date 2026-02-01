<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Install Great10 Streaming</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f0f0; display: flex; justify-content: center; padding-top: 50px; }
        .installer-box { background: white; width: 500px; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; text-align: center; color: #333; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .btn:hover { background: #0056b3; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }
        .note { font-size: 0.9em; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="installer-box">
        <h1>Installation Wizard</h1>
        <p style="text-align: center; margin-bottom: 30px; color: #777;">Setup your database and admin account.</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="process.php" method="POST">
            <h3>Database Configuration</h3>
            <div class="form-group">
                <label>Database Host</label>
                <input type="text" name="db_host" value="localhost" required>
            </div>
            <div class="form-group">
                <label>Database Name</label>
                <input type="text" name="db_name" placeholder="u123456_great10" required>
            </div>
            <div class="form-group">
                <label>Database User</label>
                <input type="text" name="db_user" placeholder="u123456_admin" required>
            </div>
            <div class="form-group">
                <label>Database Password</label>
                <input type="password" name="db_pass">
            </div>

            <hr style="margin: 30px 0;">

            <h3>Admin Account Setup</h3>
            <div class="form-group">
                <label>Admin Email</label>
                <input type="text" name="admin_email" value="admin@great10.xyz" required>
            </div>
            <div class="form-group">
                <label>Admin Password</label>
                <input type="password" name="admin_pass" required>
            </div>
            
            <div class="form-group">
                <label>Site URL</label>
                <input type="text" name="site_url" value="http://<?php echo $_SERVER['HTTP_HOST']; ?>" required>
            </div>

            <button type="submit" class="btn">Install Now</button>
        </form>
    </div>
</body>
</html>
