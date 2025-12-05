<?php
// FILE: conn.php

$host = "localhost";
$user = "root";
$pass = "";
$db_name = "gmmart";

// Buat koneksi baru
$mysqli = new mysqli($host, $user, $pass, $db_name);

// Cek koneksi
if ($mysqli->connect_errno) {
    // Hentikan eksekusi dan tampilkan error koneksi
    die("Koneksi database gagal: " . $mysqli->connect_error);
}

// Set karakter set ke utf8 untuk mendukung karakter khusus
$mysqli->set_charset("utf8");

// Catatan: Tidak perlu menutup koneksi di sini, biarkan tertutup di akhir skrip
?>