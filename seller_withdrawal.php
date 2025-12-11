<?php
// FILE: seller_withdrawal.php (UPDATED)
session_start();
require "conn.php"; 

// --- CEK LOGIN DAN ROLE SELLER ---
if (!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] ?? 'customer') !== 'seller') {
    header("Location: Login.php"); 
    exit;
}

$current_user = $_SESSION["user"]; 
$store_id = $current_user['store_id'] ?? 1; // ID Toko Dummy
$active_page = 'saldo';

// --- SIMULASI DATA SALDO DARI DB (MENGGUNAKAN SESSION UNTUK PERSISTENSI) ---
// Inisialisasi saldo jika belum ada di session (Anggap ini adalah data yang diambil saat login)
if (!isset($_SESSION['store_balance'][$store_id])) {
    $_SESSION['store_balance'][$store_id] = 5825000; // Saldo Awal Simulasi
}
$current_balance = $_SESSION['store_balance'][$store_id];
$minimum_withdrawal = 50000;

// Ambil dan hapus pesan status dari session (untuk notifikasi setelah redirect)
$withdrawal_message = $_SESSION['withdrawal_message'] ?? null;
unset($_SESSION['withdrawal_message']); 

// SIMULASI RIWAYAT PENARIKAN
$withdrawal_history = [
    ['date' => '2025-11-20', 'amount' => 1000000, 'status' => 'Selesai'],
    ['date' => '2025-12-01', 'amount' => 500000, 'status' => 'Diproses'],
    ['date' => '2025-12-07', 'amount' => 100000, 'status' => 'Gagal'],
];

// Fungsi untuk mendapatkan kelas badge sesuai seller.css
function get_status_class($status) {
    switch ($status) {
        case 'Selesai': return 'completed';
        case 'Diproses': return 'pending-order';
        case 'Gagal': return 'danger';
        default: return '';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Penarikan Saldo - GM'Mart Seller</title>
    
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="seller.css">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        function toggleMenu() { document.querySelector(".nav-menu").classList.toggle("active"); }
    </script>
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 600;
        }
        .alert.success { background-color: #d4edda; color: #155724; }
        .alert.error { background-color: #f8d7da; color: #721c24; }
    </style>
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
        <a href="seller_orders.php">Pesanan</a>
        <a href="seller_withdrawal.php" class="active-link">Saldo & Penarikan</a>
        <a href="seller_profile.php">Profil Toko</a>
        <a href="seller_to_customer.php" style="color: #ffc107;">Ke Akun Pelanggan</a>
    </nav>
    <form action="Logout.php" method="POST">
        <button class="logout">Log out</button>
    </form>
</header>

<main class="main-content">
    <h2 class="page-title">Saldo dan Penarikan Dana</h2>

    <?php if ($withdrawal_message): ?>
        <div class="alert <?= htmlspecialchars($withdrawal_message['type']) ?>">
            <?= $withdrawal_message['text'] ?>
        </div>
    <?php endif; ?>
    
    <div class="balance-card">
        <div class="current-balance">
            <h3>Saldo Tersedia</h3>
            <p>Rp <?= number_format($current_balance, 0, ',', '.') ?></p>
        </div>
        
        <form method="POST" action="withdrawal_process.php">
            <h3 style="color: #00bcd4; border-bottom: 1px solid #333; padding-bottom: 10px; margin-top: 30px;">Ajukan Penarikan</h3>
            <p style="color: #bbb; margin-top: 15px; font-size: 0.9em;">
                Minimum penarikan: **Rp <?= number_format($minimum_withdrawal, 0, ',', '.') ?>**
            </p>
            
            <div class="form-group" style="margin-top: 20px;">
                <label for="amount">Jumlah Penarikan (Rp):</label>
                <input type="number" id="amount" name="amount" 
                       min="<?= $minimum_withdrawal ?>" 
                       max="<?= $current_balance ?>" 
                       placeholder="Minimal <?= number_format($minimum_withdrawal, 0, ',', '.') ?>" required>
            </div>
            
            <button type="submit" class="withdraw-button">Tarik Dana Sekarang</button>
        </form>
    </div>

    <h3 style="color: #00bcd4; margin-top: 50px;">Riwayat Penarikan Dana</h3>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($withdrawal_history as $history): ?>
                <tr>
                    <td><?= htmlspecialchars($history['date']) ?></td>
                    <td>Rp <?= number_format($history['amount'], 0, ',', '.') ?></td>
                    <td>
                        <span class="status-badge <?= get_status_class($history['status']) ?>">
                            <?= htmlspecialchars($history['status']) ?>
                        </span>
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
