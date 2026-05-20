<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "portalrev";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    // Seharusnya tidak akan pernah terjadi jika init.php berjalan
    die("Koneksi gagal: " . $conn->connect_error);
}
?>