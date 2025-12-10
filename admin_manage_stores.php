<?php
// FILE: admin_manage_stores.php
session_start();
// CEK LOGIN DAN ROLE
if (!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] ?? 'customer') !== 'admin') {
    header("Location: Login.php"); 
    exit;
}

require "conn.php"; // Koneksi database
$current_user = $_SESSION["user"]; 
$cart_count = count($_SESSION['cart'] ?? []);

$message = '';
$error = '';

// --- LOGIKA VERIFIKASI / PENOLAKAN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['store_id'])) {
    $store_id = (int)$_POST['store_id'];
    $seller_id = (int)$_POST['seller_id'];
    $action = $_POST['action'] ?? '';
    
    $new_status = ($action === 'verify') ? 'verified' : 'rejected';

    $mysqli->begin_transaction();
    try {
        // 1. Update status di tabel stores
        $stmt_store = $mysqli->prepare("UPDATE stores SET store_status = ? WHERE id = ?");
        $stmt_store->bind_param("si", $new_status, $store_id);
        $stmt_store->execute();
        $stmt_store->close();

        // 2. Jika diverifikasi, update role user menjadi 'seller'
        if ($new_status === 'verified') {
            $stmt_user = $mysqli->prepare("UPDATE users SET role = 'seller', store_id = ? WHERE id = ?");
            $stmt_user->bind_param("ii", $store_id, $seller_id);
            $stmt_user->execute();
            $stmt_user->close();
            
            $message = "Toko berhasil diverifikasi. Penjual telah diubah role-nya menjadi 'seller'.";
        } elseif ($new_status === 'rejected') {
            // Jika ditolak, set role user kembali ke 'customer' (jika sebelumnya seller)
            $stmt_user = $mysqli->prepare("UPDATE users SET role = 'customer', store_id = NULL WHERE id = ?");
            $stmt_user->bind_param("i", $seller_id);
            $stmt_user->execute();
            $stmt_user->close();
            
            $message = "Aplikasi toko berhasil ditolak.";
        }
        
        $mysqli->commit();
        header("Location: admin_manage_stores.php?msg=" . urlencode($message));
        exit;
        
    } catch (Exception $e) {
        $mysqli->rollback();
        $error = "Gagal memproses aksi: " . $e->getMessage();
    }
}
// --- AKHIR LOGIKA POST ---

// --- PENGAMBILAN DATA TOKO PENDING ---
$sql_pending_stores = "
    SELECT s.id, s.store_name, s.store_description, s.created_at, u.id AS seller_id, u.nama AS seller_name, u.email
    FROM stores s
    JOIN users u ON s.seller_id = u.id
    WHERE s.store_status = 'pending'
    ORDER BY s.created_at ASC
";
$result_pending_stores = $mysqli->query($sql_pending_stores);
$pending_stores = $result_pending_stores->fetch_all(MYSQLI_ASSOC);

// --- PENGAMBILAN DATA RIWAYAT TOKO (Verified/Rejected) ---
$sql_history_stores = "
    SELECT s.store_name, s.store_status, s.created_at, u.nama AS seller_name
    FROM stores s
    JOIN users u ON s.seller_id = u.id
    WHERE s.store_status IN ('verified', 'rejected')
    ORDER BY s.created_at DESC
    LIMIT 10
";
$result_history_stores = $mysqli->query($sql_history_stores);
$history_stores = $result_history_stores->fetch_all(MYSQLI_ASSOC);

$mysqli->close(); // Tutup koneksi setelah semua data diambil

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Verifikasi Toko - GM'Mart Admin</title>
    
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="admin.css">
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
        <h1 class="brand"><span class="cyan">GM'</span>Mart - ADMIN</h1>
    </div>
    <div class="menu-toggle" onclick="toggleMenu()">
        <div></div> <div></div> <div></div>
    </div>
    <nav class="nav-menu">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_manage_users.php">Manajemen Pengguna</a>
        <a href="admin_manage_stores.php" class="active-link">Verifikasi Toko</a>
    </nav>
    <form action="Logout.php" method="POST">
        <button class="logout">Log out</button>
    </form>
</header>

<main class="main-content">
    <h2 class="page-title">Halaman Verifikasi Toko</h2>
    
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section class="store-verification">
        <h3>Aplikasi Toko Menunggu (<?= count($pending_stores) ?>)</h3>
        
        <?php if (!empty($pending_stores)): ?>
            <?php foreach ($pending_stores as $store): ?>
                <div class="verification-card pending">
                    <div class="card-header">
                        <h4><?= htmlspecialchars($store['store_name']); ?></h4>
                        <p class="status-badge pending">PENDING</p>
                    </div>
                    <p><strong>Deskripsi:</strong> <?= htmlspecialchars($store['store_description']); ?></p>
                    <p><strong>Pemohon:</strong> <?= htmlspecialchars($store['seller_name']); ?> (<?= htmlspecialchars($store['email']); ?>)</p>
                    <p><strong>Tanggal Daftar:</strong> <?= date('d M Y H:i', strtotime($store['created_at'])); ?></p>
                    
                    <div class="card-actions">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="store_id" value="<?= $store['id']; ?>">
                            <input type="hidden" name="seller_id" value="<?= $store['seller_id']; ?>">
                            <button type="submit" name="action" value="verify" class="btn-verify">Verifikasi</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="store_id" value="<?= $store['id']; ?>">
                            <input type="hidden" name="seller_id" value="<?= $store['seller_id']; ?>">
                            <button type="submit" name="action" value="reject" class="btn-reject">Tolak</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>Tidak ada aplikasi toko yang menunggu verifikasi saat ini.</p>
            </div>
        <?php endif; ?>
    </section>
    
    <section class="store-history">
        <h3>Riwayat 10 Verifikasi Terakhir</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Toko</th>
                    <th>Pemilik</th>
                    <th>Status</th>
                    <th>Tanggal Daftar</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history_stores as $history): ?>
                    <tr>
                        <td><?= htmlspecialchars($history['store_name']); ?></td>
                        <td><?= htmlspecialchars($history['seller_name']); ?></td>
                        <td><span class="status-badge <?= $history['store_status'] ?>"><?= strtoupper($history['store_status']) ?></span></td>
                        <td><?= date('d M Y', strtotime($history['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<footer class="footer">
    © 2025 GM'Mart. Admin Panel.
</footer>

</body>
</html>