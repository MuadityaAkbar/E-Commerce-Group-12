<?php
// FILE: History.php
session_start();

// CEK LOGIN
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

// Catatan: Jika Anda sudah memiliki tabel Riwayat Transaksi (misal: 'transactions'), 
// Anda bisa mengganti dummy data ini dengan query ke database:
// require "conn.php";
// $user_id = $_SESSION["user"]["id"];
// $result = $mysqli->query("SELECT * FROM transactions WHERE user_id = '$user_id'");
// $history = $result->fetch_all(MYSQLI_ASSOC);
// $mysqli->close();


// ====== DUMMY DATA HISTORY (Sementara) ======
$history = [
    [
        "image" => "Gaming mouse.jpg",
        "product" => "Gaming Mouse",
        "price" => 150000,
        "quantity" => 1
    ],
    [
        "image" => "headset.jpg",
        "product" => "Headset Gaming",
        "price" => 320000,
        "quantity" => 1
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>History - GM'Mart</title>
    <link rel="stylesheet" href="history.css">
</head>
<body>

<h2 class="page-title">Riwayat Transaksi Pengguna: <?= htmlspecialchars($_SESSION["user"]["nama"]) ?></h2>

<div class="history-container">

    <?php foreach ($history as $item): ?>
    
        <div class="history-card">
            
            <img src="<?= htmlspecialchars($item['image']); ?>" alt="Product Image">

            <div class="history-info">
                <h3><?= htmlspecialchars($item['product']); ?></h3>
                <p><strong>Price:</strong> Rp <?= number_format($item['price'], 0, ',', '.'); ?></p>
                <p><strong>Quantity:</strong> <?= htmlspecialchars($item['quantity']); ?></p>
                <p class="success-msg">Terima Kasih, produk berhasil dipesan!!</p>
            </div>

        </div>

    <?php endforeach; ?>

</div>

</body>
</html>