<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi ini akan dipanggil di setiap halaman
// untuk menentukan kelas CSS yang akan diterapkan pada tag <body>.
function get_theme_class() {
    return ($_SESSION['theme'] ?? 'light') === 'dark' ? 'dark-mode' : '';
}

// Menyiapkan variabel untuk digunakan di halaman
$theme_class = get_theme_class();
$is_dark_theme = ($_SESSION['theme'] ?? 'light') === 'dark';
?>