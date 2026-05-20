<?php
require_once '../includes/koneksi.php';
require_once '../includes/functions.php';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'hapus_folder' &&
    isset($_POST['id_fold'])
) {
    if (!isset($_SESSION['user_id'])) {
        die("Akses ditolak.");
    }

    $id = intval($_POST['id_fold']);
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'Admin';

    // Ambil data lengkap folder termasuk nama departemen dan tahun
    $stmt = $koneksi->prepare("
        SELECT a.id_fold, a.folder_path, b.name as dept_name, c.tahun as tahun_val 
        FROM folder a 
        JOIN departements b ON a.id_dep = b.id 
        JOIN tahun c ON a.id_tahun = c.id_tahun 
        WHERE a.id_fold = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        die("Folder tidak ditemukan.");
    }

    $dept_name = $data['dept_name'];
    $tahun_val = $data['tahun_val'];
    $folder = $data['folder_path'];

    // --- PREVENTIF CEK ---
    // 1. Cek di database documents
    $stmt_doc = $koneksi->prepare("SELECT COUNT(*) as total FROM documents WHERE nama_dept = ? AND tahun = ?");
    $stmt_doc->bind_param("ss", $dept_name, $tahun_val);
    $stmt_doc->execute();
    $res_doc = $stmt_doc->get_result();
    $row_doc = $res_doc->fetch_assoc();

    if ($row_doc['total'] > 0) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal: Ada file didalamnya (database).</div>";
        header("Location: manage_documentsrevbaru.php");
        exit;
    }

    // 2. Cek fisik folder
    if (!empty($folder) && is_dir($folder)) {
        $files = array_diff(scandir($folder), array('.', '..'));
        if (count($files) > 0) {
            $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal: Ada file didalamnya (fisik).</div>";
            header("Location: manage_documentsrevbaru.php");
            exit;
        }
    }

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        $del = $koneksi->prepare("DELETE FROM folder WHERE id_fold = ?");
        $del->bind_param("i", $id);

        if ($del->execute()) {
            // Hapus folder fisik jika kosong (seharusnya sudah dipastikan kosong di atas)
            if (!empty($folder) && is_dir($folder)) {
                rmdir($folder);
            }

            logActivity($koneksi, $user_id, 'delete_folder', "Admin {$username} menghapus folder: " . htmlspecialchars($dept_name) . " (" . htmlspecialchars($tahun_val) . ")");

            mysqli_commit($koneksi);
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Folder " . htmlspecialchars($dept_name) . " tahun " . htmlspecialchars($tahun_val) . " berhasil dihapus.</div>";
            header("Location: manage_documentsrevbaru.php");
            exit;
        } else {
            throw new Exception("Gagal menghapus data folder dari database.");
        }
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        die("Terjadi kesalahan: " . $e->getMessage());
    }
} else {
    die("Request tidak valid.");
}
