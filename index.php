<?php
// FILE: index.php
session_start();

// Jika sudah login, arahkan ke dashboard.
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

// Jika belum login, arahkan ke login.
header('Location: login.php');
exit;
?>
<!DOCTYPE html>
<html>
<body>
    <p>Loading...</p>
    <p>Klik <a href="login.php">di sini</a> jika tidak diarahkan.</p>
</body>
</html>