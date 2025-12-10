<?php
// FILE: withdrawal_process.php (UPDATED - Logika Pengurangan Saldo)
session_start();
require "conn.php"; 

// --- 1. CEK LOGIN DAN ROLE SELLER ---
if (!isset($_SESSION["user"]) || ($_SESSION["user"]["role"] ?? 'customer') !== 'seller') {
    header("Location: Login.php"); 
    exit;
}

// --- 2. CEK METODE REQUEST ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: seller_withdrawal.php");
    exit;
}

// --- 3. AMBIL DATA DARI FORM DAN SESSION ---
$store_id = $_SESSION["user"]["store_id"] ?? 1; 
$requested_amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);

// --- SIMULASI PENGAMBILAN SALDO DARI SESSION ---
if (!isset($_SESSION['store_balance'][$store_id])) {
    // Jika saldo belum ada di session (harusnya sudah diinisiasi di seller_withdrawal.php)
    $_SESSION['store_balance'][$store_id] = 5825000; 
}
$current_balance = $_SESSION['store_balance'][$store_id];
$minimum_withdrawal = 50000;
// ---------------------------------------------------

// --- 4. VALIDASI INPUT ---
if ($requested_amount === false || $requested_amount <= 0) {
    $_SESSION['withdrawal_message'] = ['type' => 'error', 'text' => 'Jumlah penarikan tidak valid.'];
    header("Location: seller_withdrawal.php");
    exit;
}

if ($requested_amount < $minimum_withdrawal) {
    $_SESSION['withdrawal_message'] = ['type' => 'error', 'text' => 'Gagal: Jumlah penarikan minimal adalah Rp ' . number_format($minimum_withdrawal, 0, ',', '.')];
    header("Location: seller_withdrawal.php");
    exit;
}

if ($requested_amount > $current_balance) {
    $_SESSION['withdrawal_message'] = ['type' => 'error', 'text' => 'Gagal: Saldo Anda tidak mencukupi untuk jumlah tersebut (Rp ' . number_format($current_balance, 0, ',', '.') . ').'];
    header("Location: seller_withdrawal.php");
    exit;
}

// --- 5. PROSES TRANSAKSI & PENGURANGAN SALDO (SIMULASI) ---

try {
    // 5a. (Nyata) Masukkan Permintaan Penarikan ke Tabel withdrawal_requests
    // ... Logika INSERT INTO withdrawal_requests ...

    // ** BAGIAN KUNCI: KURANGI SALDO DI SESSION **
    $_SESSION['store_balance'][$store_id] -= $requested_amount;
    
    // 5b. (Nyata) Commit Transaksi Database jika semua berhasil
    
    // --- 6. SET PESAN SUKSES DAN REDIRECT ---
    $success_text = "Permintaan penarikan dana sebesar **Rp " . number_format($requested_amount, 0, ',', '.') . "** berhasil diajukan. Saldo Anda telah dikurangi.";
    $_SESSION['withdrawal_message'] = ['type' => 'success', 'text' => $success_text];
    
} catch (Exception $e) {
    // 5c. (Nyata) Rollback Transaksi jika terjadi error
    
    error_log("Withdrawal failed for store $store_id: " . $e->getMessage());
    $_SESSION['withdrawal_message'] = ['type' => 'error', 'text' => 'Terjadi kesalahan sistem saat memproses penarikan. Silakan coba lagi.'];
}

// --- 7. FINAL REDIRECT ---
header("Location: seller_withdrawal.php");
exit;
?>