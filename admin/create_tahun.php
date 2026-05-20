<?php
require_once '../includes/db_connect.php';
require_once '../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tahun = mysqli_real_escape_string($koneksi, $_POST['tahun_input']);

    // Validasi: pastikan tahun tidak kosong dan valid
    if (empty($tahun)) {
        die("Tahun tidak boleh kosong.");
    }

    // Cek apakah tahun sudah ada
    $check_tahun = mysqli_query($koneksi, "SELECT * FROM tahun WHERE tahun = '$tahun'");
    if (mysqli_num_rows($check_tahun) > 0) {
        die("Tahun ini sudah ada di dalam database.");
    }

    // Base folder path for the year
    $year_folder_path = "../uploads/$tahun";

    // Insert tahun ke tabel
    $query1 = mysqli_query($koneksi, "SELECT IF(MAX(CAST(id_tahun AS UNSIGNED)) IS NULL, 1, MAX(CAST(id_tahun AS UNSIGNED)) + 1) AS nomer FROM `tahun`");
    $row1 = mysqli_fetch_assoc($query1);
    $id_tahun = $row1['nomer'];

    // Insert ke tabel tahun
    // Set folder_path di tabel tahun ke root tahun tersebut
    $query = "INSERT INTO tahun (id_tahun, tahun, folder_path) VALUES ('$id_tahun', '$tahun', '$year_folder_path')";
    
    if (mysqli_query($koneksi, $query)) {
        // 1. Buat folder fisik untuk tahun (root)
        if (!is_dir($year_folder_path)) {
            mkdir($year_folder_path, 0775, true);
        }

        // 2. Ambil semua departemen untuk membuat sub-folder otomatis
        $dept_query = mysqli_query($koneksi, "SELECT id, name FROM departements");
        
        while ($dept = mysqli_fetch_assoc($dept_query)) {
            $id_dep = $dept['id'];
            $nama_dep = $dept['name'];
            
            // Path untuk folder departemen di dalam tahun
            $dept_folder_path = $year_folder_path . "/" . $nama_dep;

            // Cek apakah sudah ada datanya (redundant check for safety)
            $cek_folder = mysqli_query($koneksi, "SELECT id_fold FROM folder WHERE id_dep = '$id_dep' AND id_tahun = '$id_tahun'");
            if (mysqli_num_rows($cek_folder) == 0) {
                 // Generate ID Folder baru
                $q_max = mysqli_query($koneksi, "SELECT IF(MAX(CAST(id_fold AS UNSIGNED)) IS NULL, 1, MAX(CAST(id_fold AS UNSIGNED)) + 1) AS nomer FROM `folder`");
                $r_max = mysqli_fetch_assoc($q_max);
                $id_fold = $r_max['nomer'];

                // Insert ke tabel folder (mapping tahun-dept)
                $insert_folder = "INSERT INTO folder (id_fold, id_dep, id_tahun, folder_path) VALUES ($id_fold, $id_dep, $id_tahun, '$dept_folder_path')";
                mysqli_query($koneksi, $insert_folder);
            }

            // Buat folder fisik departemen
            if (!is_dir($dept_folder_path)) {
                mkdir($dept_folder_path, 0775, true);
            }
        }

        header("Location: manage_documentsrevbaru.php"); 
        exit;

        header("Location: manage_documentsrevbaru.php"); 
        exit;
    } else {
        echo "Gagal menyimpan data tahun.";
    }
} else {
    echo "Akses tidak sah.";
}
?>