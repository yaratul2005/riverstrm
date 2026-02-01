<?php
// login.php

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($action === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php?page=dashboard');
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } elseif ($action === 'register') {
        $username = $_POST['username'];
        // Check if exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Email already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hash])) {
                $success = "Registration successful! Please login.";
            } else {
                $error = "Registration failed.";
            }
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-form">
        <div style="text-align: center; margin-bottom: 20px;">
            <button onclick="toggleAuth('login')" id="btn-login" class="btn" style="background: white; color: black;">Login</button>
            <button onclick="toggleAuth('register')" id="btn-register" class="btn">Register</button>
        </div>

        <?php if ($error): ?>
            <div style="color: red; margin-bottom: 10px; text-align: center;"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div style="color: green; margin-bottom: 10px; text-align: center;"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" id="login-form">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn" style="width: 100%;">Sign In</button>
        </form>

        <!-- Register Form -->
        <form method="POST" id="register-form" style="display: none;">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn" style="width: 100%;">Register</button>
        </form>
    </div>
</div>

<script>
function toggleAuth(type) {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const btnLogin = document.getElementById('btn-login');
    const btnRegister = document.getElementById('btn-register');

    if (type === 'login') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
        btnLogin.style.background = 'white';
        btnLogin.style.color = 'black';
        btnRegister.style.background = 'transparent';
        btnRegister.style.color = 'white';
    } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
        btnLogin.style.background = 'transparent';
        btnLogin.style.color = 'white';
        btnRegister.style.background = 'white';
        btnRegister.style.color = 'black';
    }
}
</script>
