<?php
// FILE: History.php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: Login.php"); 
    exit;
}
$current_user = $_SESSION["user"]; 

$product_image_map = [
    "Keyboard Mekanik" => "keyboard.jpg",
    "Monitor Gaming 24\"" => "monitor.jpg",
    "Prosesor i7 Terbaru" => "cpu.jpg",
    "Gaming Mouse" => "mouse.jpg",
    "Headset Gaming" => "headset.jpg",
    "Gaming Mouse X99" => "mouse.jpg", // Dari Flash Sale
    "Headset PRO-1" => "headset.jpg", // Dari Diskon
    "Monitor Ultra-Wide" => "monitor.jpg", // Dari Diskon
    "Keyboard Mini RGB" => "keyboard.jpg", // Dari Diskon
];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_order'])) {
    $new_order_items = $_SESSION['cart'] ?? [];
    $total_paid = $_POST['grand_total'] ?? 0; 

    if (!empty($new_order_items)) {
        $transaction_id = "TRX" . time();
        
        $new_transaction = [
            'id' => $transaction_id,
            'date' => date('Y-m-d H:i:s'),
            'total_paid' => $total_paid, 
            'status' => 'Selesai', 
            'items' => $new_order_items, 
            'address' => $_POST['address'] ?? 'Alamat Tidak Diketahui'
        ];

        if (!isset($_SESSION['history_transactions'])) {
            $_SESSION['history_transactions'] = [];
        }
        array_unshift($_SESSION['history_transactions'], $new_transaction);

        unset($_SESSION['cart']);
        
        header("Location: History.php?status=success");
        exit;
    }
}


// Ambil riwayat transaksi yang tersimpan
$transactions = $_SESSION['history_transactions'] ?? [];

$cart_count = count($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>History - GM'Mart</title>
    
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="history.css"> 
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
        <a href="History.php" class="active-link">History</a>
        <a href="cart.php">Cart (<?= $cart_count ?>)</a>
    </nav>
    <form action="Logout.php" method="POST">
        <button class="logout">Log out</button>
    </form>
</header>

<main class="main-content">
    <h2 class="page-title">Riwayat Transaksi</h2>
    
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <div class="alert-success">
            🎉 Pembayaran Berhasil! Pesanan Anda telah tercatat dalam riwayat transaksi.
        </div>
    <?php endif; ?>

    <div class="history-container">
        <?php if (!empty($transactions)): ?>
            <?php foreach ($transactions as $transaction): ?>
                <div class="history-card">
                    <div class="transaction-header">
                        <h3>Transaksi ID: <?= htmlspecialchars($transaction['id']); ?></h3>
                        <p class="status-msg success">Status: <?= htmlspecialchars($transaction['status']); ?></p>
                    </div>
                    
                    <p><strong>Tanggal Transaksi:</strong> <?= htmlspecialchars($transaction['date']); ?></p>
                    <p><strong>Alamat Pengiriman:</strong> <?= htmlspecialchars($transaction['address']); ?></p>
                    <div class="item-list">
                        <h4>Detail Produk:</h4>
                        <?php 
                        $total_items_price = 0;
                        foreach ($transaction['items'] as $item): 
                            $clean_price = str_replace('.', '', $item['price']);
                            $subtotal = (int)$clean_price * (int)$item['quantity'];
                            $total_items_price += $subtotal;
                            
                            // BARU: Ambil path gambar
                            $image_file = $product_image_map[$item['name']] ?? 'default.jpg';
                        ?>
                            <div class="history-item">
                                <img src="<?= htmlspecialchars($image_file); ?>" alt="<?= htmlspecialchars($item['name']); ?>" class="item-image">
                                
                                <div class="item-details">
                                    <p class="item-name"><?= htmlspecialchars($item['name']); ?> (x<?= htmlspecialchars($item['quantity']); ?>)</p>
                                    <p class="item-price">Rp <?= number_format($subtotal, 0, ',', '.'); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="transaction-footer">
                        <p><strong>Total Harga Barang:</strong></p>
                        <p><strong>Rp <?= number_format($total_items_price, 0, ',', '.'); ?></strong></p>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <p class="empty-msg">Belum ada riwayat transaksi.</p>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">
    © 2025 GM'Mart. All rights reserved.
</footer>

</body>
</html>
