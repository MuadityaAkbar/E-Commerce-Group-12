<?php
// FILE: seller_products.php
session_start();
require "conn.php"; // Pastikan koneksi DB tersedia

// --- CEK LOGIN DAN ROLE SELLER ---
if (!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] ?? 'customer') !== 'seller') {
    header("Location: Login.php"); 
    exit;
}

$current_user = $_SESSION["user"]; 
$store_id = $current_user['store_id'] ?? 1; // ID Toko Dummy
$active_page = 'produk';

// SIMULASI DATA PRODUK DARI DB
$products = [
    ['id' => 101, 'name' => 'Keyboard Mekanik Pro', 'stock' => 15, 'price' => '750.000', 'status' => 'Aktif'],
    ['id' => 102, 'name' => 'Monitor Gaming 24"', 'stock' => 5, 'price' => '2.500.000', 'status' => 'Aktif'],
    ['id' => 103, 'name' => 'Kabel Adapter USB-C', 'stock' => 0, 'price' => '120.000', 'status' => 'Nonaktif'],
];

$action = $_GET['action'] ?? 'list'; // 'list', 'add', 'edit'
$product_data = ['name' => '', 'price' => '', 'stock' => '', 'description' => '', 'status' => 'Aktif'];

// SIMULASI MENGAMBIL DATA UNTUK EDIT
if ($action === 'edit' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    // Dalam implementasi nyata, ambil data dari DB
    $product_data = ['name' => 'Monitor Gaming 24"', 'price' => '2.500.000', 'stock' => 5, 'description' => 'Monitor refresh rate tinggi.', 'status' => 'Aktif'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Kelola Produk - GM'Mart Seller</title>
    
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
        <a href="seller_products.php" class="active-link">Produk</a>
        <a href="seller_orders.php">Pesanan</a>
        <a href="seller_balance.php">Saldo</a>
        <a href="seller_profile.php">Profil Toko</a>
        <a href="seller_to_customer.php" style="color: #ffc107;">Ke Akun Pelanggan</a>
    </nav>
    <form action="Logout.php" method="POST">
        <button class="logout">Log out</button>
    </form>
</header>

<main class="main-content">
    <h2 class="page-title">Manajemen Produk</h2>
    
    <?php if ($action === 'list'): ?>
        <a href="seller_products.php?action=add" class="btn-primary" style="margin-bottom: 20px;">+ Tambah Produk Baru</a>
        
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product['id'] ?></td>
                        <td style="white-space: normal;"><?= htmlspecialchars($product['name']) ?></td>
                        <td>Rp <?= htmlspecialchars($product['price']) ?></td>
                        <td><?= $product['stock'] ?></td>
                        <td>
                            <span class="status-badge <?= $product['status'] === 'Aktif' ? 'active' : 'inactive' ?>">
                                <?= $product['status'] ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <a href="seller_products.php?action=edit&id=<?= $product['id'] ?>" class="btn-primary">Edit</a>
                            <form method="POST" action="product_process.php" onsubmit="return confirm('Yakin ingin menghapus produk ini?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <button type="submit" class="btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($action === 'add' || $action === 'edit'): 
        $title = ($action === 'add') ? 'Tambah Produk Baru' : 'Edit Produk ID: ' . $_GET['id'];
    ?>
        <h3 style="color: #00bcd4;"><?= $title ?></h3>
        <div class="form-container">
            <form method="POST" action="product_process.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?= $action ?>">
                <?php if ($action === 'edit'): ?><input type="hidden" name="product_id" value="<?= $_GET['id'] ?>"><?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Nama Produk:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($product_data['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="price">Harga (Rp):</label>
                    <input type="text" id="price" name="price" value="<?= htmlspecialchars($product_data['price']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stok:</label>
                    <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($product_data['stock']) ?>" min="0" required>
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi:</label>
                    <textarea id="description" name="description" required><?= htmlspecialchars($product_data['description']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Gambar Produk <?= ($action === 'edit' ? '(Biarkan kosong jika tidak diubah)' : '') ?>:</label>
                    <input type="file" id="image" name="image" <?= ($action === 'add' ? 'required' : '') ?>>
                </div>
                <div class="form-group">
                    <label for="status">Status Produk:</label>
                    <select id="status" name="status">
                        <option value="Aktif" <?= $product_data['status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                        <option value="Nonaktif" <?= $product_data['status'] === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 15px;">Simpan Produk</button>
            </form>
        </div>
        <a href="seller_products.php" style="color: #00bcd4; display: block; text-align: center; margin-top: 20px;">← Kembali ke Daftar Produk</a>
    <?php endif; ?>

</main>

<footer class="footer">
    © 2025 GM'Mart. Seller Panel.
</footer>
</body>
</html>