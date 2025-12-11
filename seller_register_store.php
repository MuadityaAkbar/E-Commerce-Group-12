<?php
session_start();
require_once 'conn.php';

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$current_user = $_SESSION["user"];
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $store_name = trim($_POST['store_name']);
    
    if (empty($store_name)) {
        $error = "Nama toko tidak boleh kosong.";
    } else {
        $stmt = $mysqli->prepare("INSERT INTO stores (seller_id, store_name, store_status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("is", $current_user['id'], $store_name);
        
        if ($stmt->execute()) {
            $new_store_id = $mysqli->insert_id;
            
            // Update user
            $stmt2 = $mysqli->prepare("UPDATE users SET role='seller', store_id=? WHERE id=?");
            $stmt2->bind_param("ii", $new_store_id, $current_user['id']);
            $stmt2->execute();
            $stmt2->close();
            
            $_SESSION['user']['role'] = 'seller';
            $_SESSION['user']['store_id'] = $new_store_id;
            
            header("Location: seller_dashboard.php");
            exit;
        } else {
            $error = "Gagal mendaftar toko.";
        }
        $stmt->close();
    }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Toko</title>
    <link rel="stylesheet" href="seller.css">
</head>
<body>
<nav>
    <h2>GM'Mart</h2>
</nav>

<main>
    <h1>Pendaftaran Toko Baru</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="form-container">
        <div class="form-group">
            <label>Nama Toko:</label>
            <input type="text" name="store_name" required>
        </div>
        
        <button type="submit" class="btn-primary">Ajukan Pendaftaran</button>
    </form>
</main>

<footer>&copy; 2025 GM'Mart.</footer>
</body>
</html>
