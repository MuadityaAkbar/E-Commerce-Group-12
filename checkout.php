<?php
// FILE: checkout.php
session_start();
// CEK LOGIN
if (!isset($_SESSION["user"])) {
    header("Location: Login.php"); 
    exit;
}

// Cek apakah keranjang kosong
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$current_user = $_SESSION["user"]; 
$cart_items = $_SESSION['cart']; 
$cart_count = count($cart_items);

// Hitung Total Harga
$total_price = 0;
foreach ($cart_items as $item) {
    $clean_price = str_replace('.', '', $item['price']);
    $subtotal = (int)$clean_price * (int)$item['quantity'];
    $total_price += $subtotal;
}

// Data simulasi pengiriman (Anda bisa mengambil ini dari profil pengguna)
$shipping_address = $current_user['alamat'] ?? 'Jl. Merdeka No. 17, Jakarta';
$shipping_cost = 25000; // Contoh biaya pengiriman
$grand_total = $total_price + $shipping_cost;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Checkout - GM'Mart</title>
    
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="checkout.css"> 
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
        <h1 class="brand"><span class="cyan">GM'</span>Mart</h1>
    </div>
    <div class="menu-toggle" onclick="toggleMenu()">
        <div></div> <div></div> <div></div>
    </div>
    <nav class="nav-menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="market.php">Market</a>
        <a href="tracking.php">Tracking</a>
        <a href="History.php">History</a>
        <a href="cart.php">Cart (<?= $cart_count ?>)</a> 
    </nav>
    <form action="Logout.php" method="POST">
        <button class="logout">Log out</button>
    </form>
</header>

<main class="main-content">
    <h2 class="page-title">Ringkasan Checkout</h2>
    <div class="checkout-container">
        
        <div class="checkout-card order-summary">
            <h3>Detail Pesanan (<?= $cart_count ?> Item)</h3>
            <?php foreach ($cart_items as $item): ?>
            <div class="summary-item">
                <p class="item-name"><?= htmlspecialchars($item['name']); ?> (x<?= $item['quantity'] ?>)</p>
                <p class="item-price">Rp <?= number_format(str_replace('.', '', $item['price']) * $item['quantity'], 0, ',', '.'); ?></p>
            </div>
            <?php endforeach; ?>
            <div class="summary-separator"></div>
            <div class="summary-total">
                <p>Harga Total Barang:</p>
                <p>Rp <?= number_format($total_price, 0, ',', '.'); ?></p>
            </div>
            <div class="summary-total">
                <p>Biaya Pengiriman:</p>
                <p>Rp <?= number_format($shipping_cost, 0, ',', '.'); ?></p>
            </div>
            <div class="summary-grand-total">
                <h4>TOTAL PEMBAYARAN:</h4>
                <h4>Rp <?= number_format($grand_total, 0, ',', '.'); ?></h4>
            </div>
        </div>

        <div class="checkout-card payment-form">
            <h3>Alamat & Pembayaran</h3>
            <form action="history.php" method="POST"> <div class="form-group">
                    <label for="address">Alamat Pengiriman:</label>
                    <textarea id="address" name="address" required><?= htmlspecialchars($shipping_address) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="method">Metode Pembayaran:</label>
                    <select id="method" name="method" required>
                        <option value="transfer">Transfer Bank (BCA/Mandiri)</option>
                        <option value="dana">E-Wallet (Dana)</option>
                        <option value="cod">COD (Bayar di Tempat)</option>
                    </select>
                </div>
                
                <button type="submit" name="confirm_order" class="confirm-btn">Konfirmasi Pesanan & Bayar</button>

            </form>
        </div>

    </div>
</main>

<footer class="footer">
    © 2025 GM'Mart. All rights reserved.
</footer>

</body>
</html>
