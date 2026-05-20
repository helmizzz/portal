<?php
// Mulai sesi (penting untuk mengakses variabel sesi)
require_once 'includes/init.php';
session_start();

// Hapus semua variabel sesi
$_SESSION = array();

// Jika ingin menghapus cookie sesi, hapus juga.
// Catatan: ini akan menghancurkan sesi, bukan hanya data sesi!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Arahkan user kembali ke halaman login
header("Location: login.php");
exit();
?>