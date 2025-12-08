// FILE: market.php
<?php
session_start();
// CEK LOGIN
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}
// Data dummy produk
$products = [
    ["id" => 1, "image" => "keyboard.jpg", "name" => "Keyboard Mekanik", "price" => "750.000", "stock" => 15],
    ["id" => 2, "image" => "monitor.jpg", "name" => "Monitor Gaming 24\"", "price" => "2.500.000", "stock" => 8],
    ["id" => 3, "image" => "cpu.jpg", "name" => "Prosesor i7 Terbaru", "price" => "4.200.000", "stock" => 5],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Market - GM'Mart</title>
    <link rel="stylesheet" href="dashboard.css">
    <style> /* CSS Sederhana untuk Market */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; padding: 20px; }
        .product-card { border: 1px solid #ddd; padding: 15px; border-radius: 8px; text-align: center; }
        .product-card img { max-width: 100%; height: auto; border-radius: 4px; margin-bottom: 10px; }
        .product-card .btn-add-cart { background-color: #00bcd4; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
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
        <div></div>
        <div></div>
        <div></div>
    </div>
    <nav class="nav-menu">
        <a href="dashboard.php">Dashboard</a>
        <a href="profile.php">Profile</a>
        <a href="market.php" class="active-link">Market</a> <a href="tracking.php">Tracking</a>
        <a href="History.php">History</a>
    </nav>
    <form action="Logout.php" method="POST">
        <button class="logout">Log out</button>
    </form>
</header>

<main class="main-content">
    <h2 class="page-title">Daftar Produk</h2>
    <div class="product-grid">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                <h3><?= htmlspecialchars($product['name']); ?></h3>
                <p>Rp <?= htmlspecialchars($product['price']); ?></p>
                <p>Stok: <?= htmlspecialchars($product['stock']); ?></p>
                <form action="cart_process.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                    <button type="submit" name="add_to_cart" class="btn-add-cart">Add to Cart</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<footer class="footer">
    © 2025 <span>GM'Mart</span>. All rights reserved.
</footer>
</body>
</html>
