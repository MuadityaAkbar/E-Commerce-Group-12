<?php 
// FILE: Login.php
session_start();

// Cek jika user sudah login, redirect ke dashboard
if (isset($_SESSION["user"])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Login - GM'Mart</title>
    <link rel="stylesheet" href="gabung.css">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .msg { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
        .error { background-color: #fdd; color: #a00; border: 1px solid #a00; }
        /* Style tambahan untuk link Google agar terlihat seperti tombol */
        .btn-google-link { 
            text-decoration: none;
            display: block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="login-container">
    
    <h1 class="title"><span class="cyan">LOG</span><span class="white">IN</span></h1>

    <?php if (isset($_GET['error'])): ?>
        <div class="msg error">Email atau password salah!</div>
    <?php endif; ?>

    <form action="process_login.php" method="POST">

        <label>Email</label>
        <input type="email" name="email" placeholder="Enter your email" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Enter password" required>

        <div class="options">
            <div class="left">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember" class="remember-text">Remember Me</label>
            </div>
            <a href="#" class="forgot">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-signin">SIGN IN</button>
    </form>
    

    <p class="signup-text">
        Don't have an account? <a href="register.php">Sign up</a>
    </p>

</div>

</body>
</html>
