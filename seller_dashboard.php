<?php
session_start();
require_once 'conn.php';

// Cek login dan role
if (!isset($_SESSION["user"]) || $_SESSION["user"]["role"] !== "seller") {
    header("Location: login.php");
    exit;
}

$current_user = $_SESSION["user"];
$store_id = $current_user["store_id"];
$store_name = "Toko Anda";
$store_status = "pending";

// Ambil info toko
if ($store_id) {
    $stmt = $mysqli->prepare("SELECT store_name, store_status FROM stores WHERE id = ?");
    $stmt->bind_param("i", $store_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $store = $res->fetch_assoc();
    $stmt->close();
    
    if ($store) {
        $store_status = $store["store_status"];
        $store_name = $store["store_name"];
    }
}

$total_products = 0;
$pending_orders = 0;
$current_balance = 0;

// Statistik jika verified
if ($store_status === 'active') {
    // Total produk (jika tabel products ada)
    if ($mysqli->query("SHOW TABLES LIKE 'products'")->num_rows > 0) {
        $stmt = $mysqli->prepare("SELECT COUNT(*) FROM products WHERE store_id = ?");
        $stmt->bind_param("i", $store_id);
        $stmt->execute();
        $total_products = $stmt->get_result()->fetch_row()[0] ?? 0;
        $stmt->close();
    }

    // Saldo (jika tabel seller_balances ada)
    if ($mysqli->query("SHOW TABLES LIKE 'seller_balances'")->num_rows > 0) {
        $stmt = $mysqli->prepare("SELECT current_balance FROM seller_balances WHERE store_id = ?");
        $stmt->bind_param("i", $store_id);
        $stmt->execute();
        $current_balance = $stmt->get_result()->fetch_row()[0] ?? 0;
        $stmt->close();
    }
    
    $pending_orders = 5; // Dummy
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>GM'Mart - Seller Dashboard</title>
    <link rel="stylesheet" href="seller.css">
</head>
<body>
<nav>
    <h2>GM'Mart - SELLER</h2>
    <div>
        <a href="seller_dashboard.php">Dashboard</a>
        <a href="seller_manage_products.php">Kelola Produk</a>
        <a href="seller_manage_orders.php">Manajemen Pesanan</a>
        <a href="seller_balance.php">Saldo & Penarikan</a>
        <a href="seller_profile.php">Profil Toko</a>
        <a href="logout.php">Log out</a>
    </div>
</nav>

<main>
    <h1>Dasbor Penjual - <?= htmlspecialchars($store_name) ?></h1>

    <?php if (!$store_id): ?>
        <div class="switch-card">
            <h2>Selamat Datang!</h2>
            <p>Silakan daftarkan toko Anda untuk mulai berjualan.</p>
            <a href="seller_register_store.php" class="btn-primary">Daftar Toko</a>
        </div>
    <?php elseif ($store_status === 'pending'): ?>
        <div class="switch-card">
            <h2>Pendaftaran Sedang Diproses</h2>
            <p>Menunggu verifikasi admin.</p>
        </div>
    <?php else: ?>
        <div class="seller-stats">
            <div class="seller-stat-box success">
                <h3>Total Produk</h3>
                <p><?= $total_products ?></p>
            </div>
            <div class="seller-stat-box warning">
                <h3>Pesanan Baru</h3>
                <p><?= $pending_orders ?></p>
            </div>
            <div class="seller-stat-box info">
                <h3>Saldo</h3>
                <p>Rp <?= number_format($current_balance, 0, ',', '.') ?></p>
            </div>
        </div>

        <h2>Aksi Cepat</h2>
        <div class="link-grid">
            <a href="seller_manage_products.php" class="quick-link">Produk & Stok</a>
            <a href="seller_manage_orders.php" class="quick-link priority">Pesanan</a>
            <a href="seller_balance.php" class="quick-link">Penarikan Dana</a>
        </div>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2025 GM'Mart. Seller Panel.</p>
</footer>
</body>
</html>
