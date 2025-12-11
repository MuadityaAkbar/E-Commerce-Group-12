<?php
// FILE: market.php (UPDATED)
session_start();
// CEK LOGIN
if (!isset($_SESSION["user"])) {
    header("Location: Login.php"); 
    exit;
}

// Data dummy produk awal (Hardcoded)
$products_all = [
    ["id" => 1, "image" => "keyboard.jpg", "name" => "Keyboard Gaming", "price" => "750.000", "stock" => 15, "category" => "keyboard"],
    ["id" => 4, "image" => "Gaming mouse.jpg", "name" => "Gaming Mouse", "price" => "150.000", "stock" => 22, "category" => "mouse"],
    ["id" => 5, "image" => "headphone.jpg", "name" => "Headphone Gaming", "price" => "320.000", "stock" => 10, "category" => "headphone"],
    ["id" => 6, "image" => "mousepad.jpg", "name" => "Mousepad Besar", "price" => "50.000", "stock" => 30, "category" => "mousepad"],
];

// ----------------------------------------------------------------------------------
// ** BAGIAN KUNCI: MENGGABUNGKAN PRODUK DARI SEMUA SELLER DI SESSION **
// ----------------------------------------------------------------------------------
$seller_products_session = $_SESSION['store_products'] ?? [];

$max_hardcoded_id = 1000; // ID tertinggi yang mungkin dari produk hardcoded (untuk menghindari tabrakan ID)

foreach ($seller_products_session as $store_id => $store_products) {
    foreach ($store_products as $seller_product) {
        // Hanya tampilkan produk yang berstatus 'Aktif'
        if (($seller_product['status'] ?? 'Nonaktif') === 'Aktif') {
            
            // Format data seller agar cocok dengan struktur data market.php
            $formatted_product = [
                // Pastikan ID tidak tabrakan dengan produk hardcoded
                "id" => (int)$seller_product['id'] + $max_hardcoded_id, 
                // Karena kita tidak mengelola upload gambar, gunakan placeholder default
                "image" => "placeholder_product.jpg", 
                "name" => $seller_product['name'],
                "price" => $seller_product['price'],
                "stock" => $seller_product['stock'],
                // Beri kategori default agar bisa difilter (misal: "lain-lain")
                "category" => $seller_product['category'] ?? "lain-lain", 
            ];

            // Tambahkan ke daftar produk utama
            $products_all[] = $formatted_product;
        }
    }
}
// ----------------------------------------------------------------------------------


// Logika Filter Kategori
$selected_category = $_GET['category'] ?? 'all';
$products = [];

if ($selected_category === 'all') {
    $products = $products_all;
} else {
    foreach ($products_all as $product) {
        if ($product['category'] === $selected_category) {
            $products[] = $product;
        }
    }
}

$cart_count = count($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Market - GM'Mart</title>
    
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="market.css"> <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
        <a href="market.php" class="active-link">Market</a> 
        <a href="tracking.php">Tracking</a>
        <a href="History.php">History</a>
        <a href="cart.php">Cart (<?= $cart_count ?>)</a>
    </nav>
    <form action="Logout.php" method="POST">
        <button class="logout">Log out</button>
    </form>
</header>

<main class="main-content">
    <h2 class="page-title">Daftar Produk</h2>
    
    <div class="filter-container">
        <form method="GET" action="market.php">
            <label for="category-select">Filter Kategori:</label>
            <select name="category" id="category-select" onchange="this.form.submit()">
                <option value="all" <?= $selected_category == 'all' ? 'selected' : '' ?>>Semua Kategori</option>
                <option value="keyboard" <?= $selected_category == 'keyboard' ? 'selected' : '' ?>>Keyboard</option>
                <option value="mouse" <?= $selected_category == 'mouse' ? 'selected' : '' ?>>Mouse</option>
                <option value="headphone" <?= $selected_category == 'headphone' ? 'selected' : '' ?>>Headphone</option>
                <option value="mousepad" <?= $selected_category == 'mousepad' ? 'selected' : '' ?>>Mousepad</option>
                <option value="lain-lain" <?= $selected_category == 'lain-lain' ? 'selected' : '' ?>>Lain-lain (Dari Seller)</option>
            </select>
        </form>
        
    </div>
    <div class="product-grid">
        <?php if (empty($products)): ?>
            <p style="text-align: center; color: #ddd; grid-column: 1 / -1;">Tidak ada produk di kategori ini.</p>
        <?php endif; ?>

        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>" 
                     onerror="this.onerror=null; this.src='no_image_available.jpg';"> 
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
    © 2025 GM'Mart. All rights reserved.
</footer>

</body>
</html>
