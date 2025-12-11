<?php
session_start();
require "conn.php";

// Cek login & role admin
if (!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] ?? '') !== 'admin') {
    header("Location: Login.php");
    exit;
}

// Fungsi untuk cek apakah kolom ada di tabel
function column_exists($mysqli, $table, $column) {
    $res = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return ($res && $res->num_rows > 0);
}

// --- Jika ada POST untuk verifikasi/tolak toko ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['store_id'], $_POST['seller_id'], $_POST['action'])) {
    $store_id  = (int)$_POST['store_id'];
    $seller_id = (int)$_POST['seller_id'];
    $action    = $_POST['action'];
    $new_status = ($action === 'verify' ? 'verified' : 'rejected');

    // Cek apakah kolom store_status ada
    if (column_exists($mysqli, 'stores', 'store_status')) {
        $stmt = $mysqli->prepare("UPDATE stores SET store_status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $store_id);
        $stmt->execute();
        $stmt->close();
    }
    // Update user role dan store_id sesuai action
    if ($new_status === 'verified') {
        $stmt2 = $mysqli->prepare("UPDATE users SET role = 'seller', store_id = ? WHERE id = ?");
        $stmt2->bind_param("ii", $store_id, $seller_id);
        $stmt2->execute();
        $stmt2->close();
    } else {
        $stmt2 = $mysqli->prepare("UPDATE users SET role = 'customer', store_id = NULL WHERE id = ?");
        $stmt2->bind_param("i", $seller_id);
        $stmt2->execute();
        $stmt2->close();
    }

    header("Location: admin_manage_stores.php");
    exit;
}

// --- Ambil data toko (jika tabel stores ada) ---
$stores = [];
if ($mysqli->query("SHOW TABLES LIKE 'stores'")->num_rows > 0) {

    // Tentukan apakah kolom store_status ada
    $hasStatus = column_exists($mysqli, 'stores', 'store_status');

    // Kolom untuk SELECT berbeda tergantung ada atau tidak store_status
    $selectCols = "s.id AS store_id, s.store_name, u.id AS seller_id, u.username AS seller_name, u.email AS seller_email";
    if ($hasStatus) {
        $selectCols .= ", s.store_status";
    }

    $sql = "SELECT $selectCols FROM stores s LEFT JOIN users u ON u.id = s.seller_id ORDER BY s.id ASC";
    $res = $mysqli->query($sql);
    if ($res) {
        $stores = $res->fetch_all(MYSQLI_ASSOC);
    }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi / Kelola Toko - Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<header class="navbar">
    <div class="logo-title">
        <img src="Logo.jpg" class="logo">
        <h1 class="brand"><span class="cyan">GM'</span>Mart - ADMIN</h1>
    </div>
    <nav class="nav-menu">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_manage_users.php">Manajemen Pengguna</a>
        <a href="admin_manage_stores.php" class="active-link">Manajemen Toko</a>
    </nav>
    <form action="Logout.php" method="POST">
        <button class="logout">Log out</button>
    </form>
</header>

<main class="main-content">
    <h2 class="page-title">Kelola Toko</h2>

    <?php if (empty($stores)): ?>
        <p>Tidak ada data toko.</p>
    <?php else: ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID Toko</th>
                <th>Nama Toko</th>
                <th>Pemilik (User)</th>
                <th>Email Pemilik</th>
                <?php if (isset($hasStatus) && $hasStatus): ?>
                    <th>Status</th>
                <?php endif; ?>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($stores as $st): ?>
            <tr>
                <td><?= htmlspecialchars($st['store_id']); ?></td>
                <td><?= htmlspecialchars($st['store_name']); ?></td>
                <td><?= htmlspecialchars($st['seller_name']); ?></td>
                <td><?= htmlspecialchars($st['seller_email']); ?></td>
                <?php if (isset($hasStatus) && $hasStatus): ?>
                    <td><?= htmlspecialchars($st['store_status']); ?></td>
                <?php endif; ?>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="store_id" value="<?= $st['store_id']; ?>">
                        <input type="hidden" name="seller_id" value="<?= $st['seller_id']; ?>">
                        <button type="submit" name="action" value="verify">Verifikasi</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="store_id" value="<?= $st['store_id']; ?>">
                        <input type="hidden" name="seller_id" value="<?= $st['seller_id']; ?>">
                        <button type="submit" name="action" value="reject">Tolak</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>
</main>

<footer class="footer">
    © 2025 GM'Mart Admin
</footer>

</body>
</html>
