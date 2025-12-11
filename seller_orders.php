<?php
// FILE: seller_orders.php
session_start();
require "conn.php"; // Pastikan koneksi DB tersedia

// --- CEK LOGIN DAN ROLE SELLER ---
if (!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] ?? 'customer') !== 'seller') {
    header("Location: Login.php"); 
    exit;
}

$current_user = $_SESSION["user"]; 
$store_id = $current_user['store_id'] ?? 1; // ID Toko Dummy
$active_page = 'pesanan';

// SIMULASI DATA PESANAN DARI DB
// Status: Pending, Processed, Shipped, Completed
$orders = [
    ['id' => 'ORD005', 'date' => '2025-12-07', 'item_count' => 2, 'status' => 'Pending', 'customer' => 'Ani Susanti', 'address' => 'Jakarta'],
    ['id' => 'ORD004', 'date' => '2025-12-06', 'item_count' => 1, 'status' => 'Processed', 'customer' => 'Budi Hartono', 'address' => 'Bandung'],
    ['id' => 'ORD003', 'date' => '2025-12-05', 'item_count' => 3, 'status' => 'Shipped', 'customer' => 'Cici Paramida', 'address' => 'Surabaya'],
    ['id' => 'ORD002', 'date' => '2025-12-04', 'item_count' => 1, 'status' => 'Completed', 'customer' => 'Dani Eka', 'address' => 'Semarang'],
];

// Fungsi untuk mendapatkan kelas badge (Menggunakan kelas dari seller.css)
function get_status_class($status) {
    switch ($status) {
        case 'Pending': return 'pending-order';
        case 'Processed': return 'processed';
        case 'Shipped': return 'shipped';
        case 'Completed': return 'completed';
        default: return '';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Manajemen Pesanan - GM'Mart Seller</title>
    
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="seller.css">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        function toggleMenu() { document.querySelector(".nav-menu").classList.toggle("active"); }
    </script>
</head>
<body>
<header class="navbar">
    <div class="logo-title">
        <img src="Logo.jpg" class="logo">
        <h1 class="brand"><span class="cyan">GM'</span>Mart - SELLER</h1>
    </div>
    <div class="menu-toggle" onclick="toggleMenu()">
        <div></div> <div></div> <div></div>
    </div>
    <nav class="nav-menu">
        <a href="seller_dashboard.php">Dashboard</a>
        <a href="seller_products.php">Produk</a>
        <a href="seller_orders.php" class="active-link">Pesanan</a>
        <a href="seller_balance.php">Saldo</a>
        <a href="seller_profile.php">Profil Toko</a>
        <a href="seller_to_customer.php" style="color: #ffc107;">Ke Akun Pelanggan</a>
    </nav>
    <form action="Logout.php" method="POST">
        <button class="logout">Log out</button>
    </form>
</header>

<main class="main-content">
    <h2 class="page-title">Manajemen Pesanan</h2>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID Pesanan</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Alamat Kirim</th>
                    <th>Jml. Item</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']) ?></td>
                    <td><?= htmlspecialchars($order['date']) ?></td>
                    <td><?= htmlspecialchars($order['customer']) ?></td>
                    <td style="white-space: normal; max-width: 250px;"><?= htmlspecialchars($order['address']) ?></td>
                    <td><?= $order['item_count'] ?></td>
                    <td>
                        <span class="status-badge <?= get_status_class($order['status']) ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </td>
                    <td class="table-actions">
                        <?php if ($order['status'] === 'Pending'): ?>
                            <form method="POST" action="order_process.php">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <button type="submit" name="action" value="process" class="btn-primary">Proses</button>
                            </form>
                        <?php elseif ($order['status'] === 'Processed'): ?>
                            <form method="POST" action="order_process.php">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <button type="submit" name="action" value="ship" class="btn-secondary">Kirim</button>
                            </form>
                        <?php elseif ($order['status'] === 'Shipped'): ?>
                            <a href="#" class="btn-secondary" style="background-color: #3e3e3e; cursor: default;">Menunggu Diterima</a>
                        <?php else: ?>
                            <a href="#" class="btn-secondary" style="background-color: #3e3e3e; cursor: default;">Selesai</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

<footer class="footer">
    © 2025 GM'Mart. Seller Panel.
</footer>
</body>
</html>
