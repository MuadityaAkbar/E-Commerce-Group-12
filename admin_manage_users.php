<?php
// FILE: admin_manage_users.php
session_start();
require "conn.php"; 

// --- CEK LOGIN DAN ROLE ---
// Memastikan hanya user dengan role 'admin' yang bisa mengakses
if (!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] ?? 'customer') !== 'admin') {
    header("Location: Login.php"); 
    exit;
}

$current_user = $_SESSION["user"]; 
$message = '';
$error = '';

// --- LOGIKA UPDATE ROLE (POST REQUEST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id']) && isset($_POST['new_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = $_POST['new_role'];
    
    // Validasi role yang diizinkan
    $allowed_roles = ['customer', 'seller', 'admin'];
    if (!in_array($new_role, $allowed_roles)) {
        $error = "Role yang diminta tidak valid.";
    } else {
        $mysqli->begin_transaction();
        try {
            $store_id_update = 'NULL';
            
            // 1. Update role pengguna
            $stmt = $mysqli->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $user_id);
            $stmt->execute();
            $stmt->close();
            
            $message = "Role pengguna ID {$user_id} berhasil diubah menjadi '{$new_role}'.";
            
            // LOGIKA TAMBAHAN: Jika diubah menjadi non-seller, putuskan hubungan toko
            if ($new_role === 'customer' || $new_role === 'admin') {
                // Hapus store_id dari users
                $stmt_clear_store = $mysqli->prepare("UPDATE users SET store_id = NULL WHERE id = ?");
                $stmt_clear_store->bind_param("i", $user_id);
                $stmt_clear_store->execute();
                $stmt_clear_store->close();
                
                // Opsional: Set status toko menjadi 'rejected' jika ada toko yang terkait (tergantung kebijakan)
                // $stmt_reject_store = $mysqli->prepare("UPDATE stores SET store_status = 'rejected' WHERE seller_id = ? AND store_status = 'verified'");
                // ...
            }
            
            $mysqli->commit();
            header("Location: admin_manage_users.php?msg=" . urlencode($message));
            exit;
        } catch (Exception $e) {
            $mysqli->rollback();
            $error = "Gagal mengubah role: " . $e->getMessage();
        }
    }
}
// --- AKHIR LOGIKA POST ---

// --- PENGAMBILAN SEMUA DATA PENGGUNA (QUERY DIPERBAIKI) ---
// Menghilangkan 'u.tanggal_join' dari SELECT
$sql_users = "
    SELECT 
        u.id, u.nama, u.email, u.role, 
        s.store_name, s.store_status, s.id AS store_id
    FROM users u
    LEFT JOIN stores s ON u.store_id = s.id
    ORDER BY u.id ASC
";
$result_users = $mysqli->query($sql_users); // Baris ini adalah baris 73 di file sebelumnya
$all_users = $result_users->fetch_all(MYSQLI_ASSOC);

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Manajemen Pengguna - GM'Mart Admin</title>
    
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="admin.css"> <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        function toggleMenu() {
            document.querySelector(".nav-menu").classList.toggle("active");
        }
        
        // Fungsi untuk konfirmasi perubahan role
        function confirmRoleChange(userId, currentRole) {
            const newRole = document.getElementById('select_role_' + userId).value;
            if (newRole !== currentRole) {
                return confirm(`Apakah Anda yakin ingin mengubah role pengguna ID ${userId} dari '${currentRole}' menjadi '${newRole}'?`);
            }
            return false;
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
        <a href="admin_manage_users.php" class="active-link">Manajemen Pengguna</a>
        <a href="admin_manage_stores.php">Verifikasi Toko</a>
    </nav>
    <form action="Logout.php" method="POST">
        <button class="logout">Log out</button>
    </form>
</header>

<main class="main-content">
    <h2 class="page-title">Manajemen Pengguna dan Toko</h2>
    
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <section class="user-list">
        <h3>Total Pengguna Terdaftar (<?= count($all_users) ?>)</h3>
        
        <div class="table-responsive">
            <table class="data-table user-management-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role Saat Ini</th>
                        <th>Toko Terkait</th>
                        <th>Status Toko</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_users as $user): 
                        $is_admin_or_self = ($user['role'] === 'admin' && $user['id'] !== $current_user['id']);
                        $is_self = ($user['id'] === $current_user['id']);
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']); ?></td>
                        <td><?= htmlspecialchars($user['nama']); ?></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td><span class="status-badge <?= htmlspecialchars($user['role']); ?>"><?= strtoupper(htmlspecialchars($user['role'])); ?></span></td>
                        <td><?= htmlspecialchars($user['store_name'] ?? '-'); ?></td>
                        <td>
                            <?php if ($user['store_status']): ?>
                                <span class="status-badge <?= htmlspecialchars($user['store_status']); ?>">
                                    <?= strtoupper(htmlspecialchars($user['store_status'])); ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" onsubmit="return confirmRoleChange(<?= $user['id']; ?>, '<?= $user['role']; ?>');">
                                <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                <select name="new_role" id="select_role_<?= $user['id']; ?>" class="role-select" 
                                    <?= $is_admin_or_self ? 'disabled' : ''; // Admin lain dan diri sendiri tidak bisa diedit role-nya ?>
                                >
                                    <option value="customer" <?= $user['role'] == 'customer' ? 'selected' : ''; ?>>Customer</option>
                                    <option value="seller" <?= $user['role'] == 'seller' ? 'selected' : ''; ?>>Seller</option>
                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                                
                                <?php if (!$is_admin_or_self && !$is_self): ?>
                                    <button type="submit" class="btn-action">Update Role</button>
                                <?php elseif ($is_self): ?>
                                    <button type="button" class="btn-action disabled" disabled>Anda Saat Ini</button>
                                <?php else: ?>
                                    <button type="button" class="btn-action disabled" disabled>Admin Lain</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<footer class="footer">
    © 2025 GM'Mart. Admin Panel.
</footer>

</body>
</html>