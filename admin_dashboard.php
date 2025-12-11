<?php
session_start();
require "conn.php";

// Pastikan user adalah admin
if (!isset($_SESSION["user"]) || $_SESSION["user"]["role"] !== "admin") {
    header("Location: Login.php");
    exit;
}

$admin_name = $_SESSION["user"]["nama"];

// ---------------------------------------------------
// CEK TABEL ADA / GA
// ---------------------------------------------------
function table_exists($mysqli, $table) {
    $check = $mysqli->query("SHOW TABLES LIKE '$table'");
    return ($check && $check->num_rows > 0);
}

// ---------------------------------------------------
// CEK KOLOM ADA / GA
// ---------------------------------------------------
function column_exists($mysqli, $table, $column) {
    if (!table_exists($mysqli, $table)) return false;
    $check = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return ($check && $check->num_rows > 0);
}

// ---------------------------------------------------
// SAFE COUNT — ANTI ERROR
// ---------------------------------------------------
function safe_count($mysqli, $sql, $table, $column = null) {

    // CEK TABEL
    if (!table_exists($mysqli, $table)) {
        return 0;
    }

    // CEK KOLOM (kalau dipakai pada WHERE)
    if ($column !== null && !column_exists($mysqli, $table, $column)) {
        return 0;
    }

    // JALANKAN QUERY
    $result = $mysqli->query($sql);
    return ($result) ? ($result->fetch_row()[0] ?? 0) : 0;
}

// ---------------------------------------------------
// AMBIL DATA DASHBOARD
// ---------------------------------------------------
$total_users = safe_count(
    $mysqli,
    "SELECT COUNT(*) FROM users",
    "users"
);

$verified_stores = safe_count(
    $mysqli,
    "SELECT COUNT(*) FROM stores WHERE store_status = 'verified'",
    "stores",
    "store_status"
);

$pending_stores = safe_count(
    $mysqli,
    "SELECT COUNT(*) FROM stores WHERE store_status = 'pending'",
    "stores",
    "store_status"
);

$total_transactions = safe_count(
    $mysqli,
    "SELECT COUNT(*) FROM orders",
    "orders"
);

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
</head>
<body>

<header class="navbar">
    <div class="logo-title">
        <img src="Logo.jpg" class="logo">
        <h1 class="brand"><span class="cyan">GM'</span>Mart - ADMIN</h1>
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
    <h2 class="page-title">Selamat Datang, <?= htmlspecialchars($admin_name) ?>!</h2>

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
</main>

<footer class="footer">
    © 2025 GM'Mart. Admin Panel.
</footer>

</body>
</html>
