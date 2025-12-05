<?php
// FILE: dashboard.php
session_start();

// CEK LOGIN: Jika session user tidak ada, redirect ke login
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

// Data user yang sedang login
$current_user = $_SESSION["user"]; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>GM'Mart Dashboard</title>

    <link rel="stylesheet" href="dashboard.css">

    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        function toggleMenu() {
            // Logika untuk menampilkan/menyembunyikan menu pada tampilan responsif
            document.querySelector(".nav-menu").classList.toggle("active");
        }
    </script>
</head>

<body>

<header class="navbar">

    <div class="logo-title">
        <img src="Logo.jpg" class="logo">
        <h1 class="brand"><span class="cyan">GM'</span>Mart</h1>
    </div>

    <div class="menu-toggle" onclick="toggleMenu()">
        <div></div>
        <div></div>
        <div></div>
    </div>

    <nav class="nav-menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="#">Profile</a>
        <a href="#">Market</a>
        <a href="#">Tracking</a>
        <a href="History.php">History</a> 
    </nav>

    <form action="Logout.php" method="POST">
        <button class="logout">Log out</button>
    </form>

</header>


<section class="hero">
    <div class="hero-left"></div>
    <div class="hero-right"></div>

    <div class="hero-content">
        <h1>Halo, <?= htmlspecialchars($current_user["nama"]) ?>!</h1> 
        <p>Selamat Datang di <span>GM'Mart</span> — tempat terbaik untuk kebutuhan teknologi Anda!</p>
    </div>
</section>


<section class="stats">
    <div class="stat-box">
        <h3>Total Produk</h3>
        <p>0</p>
    </div>
    <div class="stat-box">
        <h3>Pesanan</h3>
        <p>0</p>
    </div>
    <div class="stat-box">
        <h3>Pendapatan</h3>
        <p>0</p>
    </div>
</section>


<footer class="footer">
    © 2025 <span>GM'Mart</span>. All rights reserved.
</footer>

</body>
</html>