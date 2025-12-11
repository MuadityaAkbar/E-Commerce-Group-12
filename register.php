<?php
// FILE: register.php

require "conn.php"; // Koneksi DB

$error = "";
$success = "";
$nama = $email = ""; // Variabel untuk input

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Ambil input
    $nama = trim($_POST["nama"]);      // akan disimpan ke kolom "username"
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];
    $cek_setuju = isset($_POST["setuju"]);

    // --- Validasi ---
    if (empty($nama) || empty($email) || empty($password) || empty($confirm)) {
        $error = "Semua kolom harus diisi.";
    } elseif ($password !== $confirm) {
        $error = "Konfirmasi password tidak cocok.";
    } elseif (!$cek_setuju) {
        $error = "Anda harus menyetujui Terms & Policy.";
    } else {

        // Cek email duplikat
        $check_stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {

            // Password hash
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // INSERT menggunakan kolom USERNAME, bukan nama!
            $insert_stmt = $mysqli->prepare("
                INSERT INTO users (username, email, password, role)
                VALUES (?, ?, ?, 'user')
            ");

            $insert_stmt->bind_param("sss", $nama, $email, $hashed_password);

            if ($insert_stmt->execute()) {
                $success = "Registrasi berhasil! Silakan <a href='login.php'>Login</a>.";
            } else {
                $error = "Terjadi kesalahan saat menyimpan data ke database.";
            }

            $insert_stmt->close();
        }

        $check_stmt->close();
    }
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Register - GM'Mart</title>
    <link rel="stylesheet" href="gabung.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .msg { padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
        .error { background-color: #fdd; color: #a00; border: 1px solid #a00; }
        .success { background-color: #dfd; color: #0a0; border: 1px solid #0a0; }
    </style>
</head>

<body>

<div class="login-container">
    <h1 class="title"><span class="cyan">SIGN</span><span class="white">UP</span></h1>

    <?php if ($error): ?>
        <div class="msg error"><?= $error; ?></div>
    <?php elseif ($success): ?>
        <div class="msg success"><?= $success; ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <label>Nama Lengkap</label>
        <input type="text" name="nama" placeholder="Enter your full name" required value="<?= htmlspecialchars($nama); ?>">

        <label>Email</label>
        <input type="email" name="email" placeholder="Enter your email" required value="<?= htmlspecialchars($email); ?>">

        <label>Password</label>
        <input type="password" name="password" placeholder="Enter password" required>

        <label>Konfirmasi Password</label>
        <input type="password" name="confirm" placeholder="Confirm password" required>

        <div class="options">
            <div class="left">
                <input type="checkbox" id="setuju" name="setuju" <?= isset($cek_setuju) && $cek_setuju ? 'checked' : ''; ?>>
                <label for="setuju">Saya menyetujui Terms & Policy</label>
            </div>
        </div>

        <button type="submit" class="btn-signin">SIGN UP</button>

        <p class="signup-text">
            Sudah punya akun? <a href="login.php">Login</a>
        </p>
    </form>
</div>

</body>
</html>
