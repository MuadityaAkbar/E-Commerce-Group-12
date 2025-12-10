<?php
// FILE: admin_dashboard.php
session_start();

// --- KONEKSI DATABASE NYATA ---
// Pastikan file ini mendefinisikan variabel koneksi database Anda, misalnya: $mysqli
require "conn.php"; 
// Jika file conn.php Anda tidak mendefinisikan $mysqli, Anda harus perbaiki conn.php Anda.

// --- CEK LOGIN DAN ROLE ---
if (!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] ?? 'customer') !== 'admin') {
    // Pengguna tidak login atau bukan admin, alihkan ke halaman login
    header("Location: Login.php"); 
    exit;
}

$current_user = $_SESSION["user"]; 
$admin_name = $current_user['nama'] ?? 'Administrator';

// --- FUNGSI BANTU UNTUK AMBIL COUNT ---
// Fungsi ini mencegah error jika query gagal atau tidak mengembalikan hasil
function get_count($mysqli, $sql) {
    $result = $mysqli->query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_row()[0] ?? 0;
    }
    return 0;
}

// --- PENGAMBILAN DATA STATISTIK DARI DB ---

// 1. Total Pengguna
$sql_users = "SELECT COUNT(id) FROM users";
$total_users = get_count($mysqli, $sql_users);

// 2. Toko Terverifikasi
$sql_verified_stores = "SELECT COUNT(id) FROM stores WHERE store_status = 'verified'";
$verified_stores = get_count($mysqli, $sql_verified_stores);

// 3. Toko Menunggu Verifikasi
$sql_pending_stores = "SELECT COUNT(id) FROM stores WHERE store_status = 'pending'";
$pending_stores = get_count($mysqli, $sql_pending_stores);

// 4. Total Transaksi
$sql_total_transactions = "SELECT COUNT(id) FROM orders";
$total_transactions = get_count($mysqli, $sql_total_transactions);

// Tutup koneksi DB setelah selesai mengambil semua data
$mysqli->close(); 

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Admin Dashboard - GM'Mart</title>
    
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="admin.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        function toggleMenu() {
            document.querySelector(".nav-menu").classList.toggle("active");
        }
    </script>
</head>
<body>

<header class="navbar">
    <div class="logo-title">
        <img src="Logo.jpg" class="logo">
        <h1 class="brand"><span class="cyan">GM'</span>Mart - ADMIN</h1>
    </div>
    <div class="menu-toggle" onclick="toggleMenu()">
        <div></div> <div></div> <div></div>
    </div>
    <nav class="nav-menu">
        <a href="admin_dashboard.php" class="active-link">Dashboard</a>
        <a href="admin_manage_users.php">Manajemen Pengguna</a>
        <a href="admin_manage_stores.php">Verifikasi Toko (<?= $pending_stores ?>)</a>
    </nav>
    <form action="Logout.php" method="POST">
        <button class="logout">Log out</button>
    </form>
</header>

<main class="main-content">
    <h2 class="page-title">Selamat Datang, <?= htmlspecialchars($admin_name); ?>!</h2>
    
    <section class="admin-stats">
        
        <div class="admin-stat-box primary">
            <h3>Total Pengguna</h3>
            <p><?= $total_users ?></p>
        </div>
        
        <div class="admin-stat-box success">
            <h3>Toko Terverifikasi</h3>
            <p><?= $verified_stores ?></p>
        </div>
        
        <div class="admin-stat-box warning">
            <h3>Toko Menunggu Verifikasi</h3>
            <p><?= $pending_stores ?></p>
        </div>
        
        <div class="admin-stat-box info">
            <h3>Total Transaksi</h3>
            <p><?= $total_transactions ?></p>
        </div>
        
    </section>

    <hr/>

    <section class="admin-quick-links">
        <h2>Aksi Cepat</h2>
        
        <div class="link-grid">
            <a href="admin_manage_users.php" class="quick-link">
                Kelola Semua Akun
            </a>
            <a href="admin_manage_stores.php" class="quick-link">
                Verifikasi Pendaftaran Toko (<?= $pending_stores ?>)
            </a>
        </div>
    </section>

</main>

<footer class="footer">
    © 2025 GM'Mart. Admin Panel.
</footer>

</body>
</html>