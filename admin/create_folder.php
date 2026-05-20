<?php
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_dep = trim($_POST['nama_dep']);
    $id_tahun = intval($_POST['nama_tahun']);

    if (empty($nama_dep) || empty($id_tahun)) {
        die("Nama departemen dan tahun wajib diisi.");
    }

    // Cek apakah departemen sudah ada
    $cek_dep = mysqli_query($koneksi, "SELECT id FROM departements WHERE name = '$nama_dep'");
    if (mysqli_num_rows($cek_dep) > 0) {
        $row = mysqli_fetch_assoc($cek_dep);
        $id_dep = $row['id'];
    } else {
        // Insert ke tabel departemen
        $query1 = mysqli_query($koneksi, "SELECT IF(MAX(CAST(id AS UNSIGNED)) IS NULL, 1, MAX(CAST(id AS UNSIGNED)) + 1) AS nomer FROM `departements`");
        $row1 = mysqli_fetch_assoc($query1);
        $id_dep = $row1['nomer'];
        mysqli_query($koneksi, "INSERT INTO departements (id, name) VALUES ('$id_dep','$nama_dep')");
    }

    // Ambil tahun dari tabel
    $tahun_q = mysqli_query($koneksi, "SELECT tahun FROM tahun WHERE id_tahun = $id_tahun");
    $tahun_data = mysqli_fetch_assoc($tahun_q);
    $tahun = $tahun_data['tahun'];

    // Buat path folder
    $folder_path = "../uploads/$tahun/" . strtolower($nama_dep);

    // Cek apakah sudah ada di 6s_folder
    $cek_folder = mysqli_query($koneksi, "SELECT * FROM folder WHERE id_dep = $id_dep AND id_tahun = $id_tahun");
    if (mysqli_num_rows($cek_folder) === 0) {
        // Insert ke tabel 6s_folder
        $query2 = mysqli_query($koneksi, "SELECT IF(MAX(CAST(id_fold AS UNSIGNED)) IS NULL, 1, MAX(CAST(id_fold AS UNSIGNED)) + 1) AS nomer FROM `folder`");
        $row2 = mysqli_fetch_assoc($query2);
        $id_fold = $row2['nomer'];
        mysqli_query($koneksi, "INSERT INTO folder (id_fold, id_dep, id_tahun, folder_path) VALUES ($id_fold, $id_dep, $id_tahun, '$folder_path')");
    }

    // Buat folder fisik jika belum ada
    if (!is_dir($folder_path)) {
        mkdir($folder_path, 0775, true);
        chown($folder_path, 'www-data');
        chgrp($folder_path, 'www-data');
    }

    header("Location: manage_documentsrevbaru.php"); // ganti sesuai halaman utama jika beda
    exit;
} else {
    echo "Akses tidak sah.";
}
?>
