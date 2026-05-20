<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika kita sudah berada di halaman instalasi untuk menghindari redirect loop
if (basename($_SERVER['PHP_SELF']) == 'install.php') {
    return;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "portalrev";

// Nonaktifkan pelaporan error default mysqli agar kita bisa menanganinya sendiri
mysqli_report(MYSQLI_REPORT_OFF);

// 1. Cek koneksi ke server MySQL
$conn_check = new mysqli($servername, $username, $password);
if ($conn_check->connect_error) {
    header("Location: install.php?status=no_server");
    exit();
}

// 2. Cek apakah database ada. Cara ini lebih aman dan tidak menyebabkan fatal error.
$db_exists_query = $conn_check->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
if ($db_exists_query->num_rows == 0) {
    $conn_check->close();
    header("Location: install.php?status=no_db");
    exit();
}

// Jika database ada, sekarang kita bisa memilihnya dengan aman
$conn_check->select_db($dbname);

// 3. Cek apakah tabel utama (misal: 'users') ada
$tables_exist_query = $conn_check->query("SHOW TABLES LIKE 'users'");
if ($tables_exist_query->num_rows == 0) {
    $conn_check->close();
    header("Location: install.php?status=no_tables");
    exit();
}

$conn_check->close();

// Aktifkan kembali pelaporan error default jika diperlukan di bagian lain aplikasi
// mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>