<?php
// Password yang ingin Anda hash
$password = 'password123'; // Ganti dengan password yang Anda inginkan

// Buat hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Tampilkan hash password
echo $hashed_password;
?>