<?php
// FILE: process_login.php
session_start();
require "conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Ambil user dari database berdasarkan email (Prepared Statement)
    $stmt = $mysqli->prepare("SELECT id, nama, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    $mysqli->close();

    // Verifikasi Password
    if ($user && password_verify($password, $user["password"])) {
        
        // Login Berhasil
        
        // Hapus password hash dari session (untuk keamanan)
        unset($user['password']); 
        
        $_SESSION["user"] = $user;
        
        header("Location: dashboard.php"); 
        exit;
    } else {
        // Login Gagal
        header("Location: login.php?error=1");
        exit;
    }
} else {
    // Jika diakses tanpa POST, redirect ke login
    header("Location: login.php");
    exit;
}
?>