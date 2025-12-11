<?php
// FILE: seller_to_customer.php
session_start();

// --- CEK LOGIN ---
if (!isset($_SESSION["user"])) {
    header("Location: Login.php"); 
    exit;
}

// --- LOGIKA ROLE SWITCH ---
// Pastikan hanya seller yang bisa kembali ke customer role
if (($_SESSION["user"]["role"] ?? 'customer') === 'seller') {
    // 1. Simpan Role Lama (Opsional, untuk memudahkan kembali)
    $_SESSION["user"]["prev_role"] = 'seller';
    
    // 2. Ubah Role menjadi customer
    $_SESSION["user"]["role"] = 'customer';
}

// Arahkan ke dashboard pelanggan (asumsi file dashboard.php ada)
header("Location: dashboard.php");
exit;
?>
