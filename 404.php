<?php
// 404.php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .error-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: radial-gradient(circle, #222 0%, #000 100%);
        }
        .error-code { font-size: 8rem; font-weight: 900; color: var(--accent-color); text-shadow: 0 0 30px rgba(229,9,20,0.5); }
        .error-msg { font-size: 2rem; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <div class="error-msg">Lost in Space?</div>
        <p>The page you are looking for does not exist.</p>
        <br>
        <a href="index.php" class="btn btn-primary">Go Home</a>
    </div>
</body>
</html>
