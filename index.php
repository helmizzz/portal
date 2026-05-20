<?php
// Mulai sesi
session_start();

// Cek apakah user sudah login atau belum dengan memeriksa variabel sesi
if (isset($_SESSION['user_id'])) {
    // Jika user sudah login, arahkan ke halaman dashboard
    header("Location: dashboard.php");
    exit();
} else {
    // Jika user belum login, arahkan ke halaman login
    header("Location: login.php");
    exit();
}
?>