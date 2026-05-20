<?php
require_once '../includes/koneksi.php';
require_once '../includes/functions.php';

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'hapus_tahun' &&
    isset($_POST['tahun_id'])
) {
    if (!isset($_SESSION['user_id'])) {
        die("Akses ditolak.");
    }

    $id = intval($_POST['tahun_id']);
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'Admin';

    // Ambil data tahun
    $stmt = $koneksi->prepare("SELECT tahun, folder_path FROM tahun WHERE id_tahun = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if (!$data) {
        die("Tahun tidak ditemukan.");
    }

    $tahun_val = $data['tahun'];
    $folder = $data['folder_path'];

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // Hapus data tahun (ini akan memicu delete cascade jika ada FK, jika tidak kita harus hapus item terkait)
        // Namun berdasarkan request user, kita hapus data di table, folder_path, dan folder fisik.

        $del = $koneksi->prepare("DELETE FROM tahun WHERE id_tahun = ?");
        $del->bind_param("i", $id);

        if ($del->execute()) {
            // Hapus folder jika ada
            if (!empty($folder) && is_dir($folder)) {
                function deleteFolderRecursive($path)
                {
                    if (is_dir($path)) {
                        $files = glob($path . '/*');
                        foreach ($files as $file) {
                            if (is_dir($file)) {
                                deleteFolderRecursive($file);
                            } else {
                                unlink($file);
                            }
                        }
                        rmdir($path);
                    }
                }

                deleteFolderRecursive($folder);

                // Juga hapus parent folder tahun jika kosong
                $parent_year_folder = dirname($folder);
                if (is_dir($parent_year_folder)) {
                    $files = glob($parent_year_folder . '/*');
                    if (empty($files)) {
                        rmdir($parent_year_folder);
                    }
                }
            }

            logActivity($koneksi, $user_id, 'delete_tahun', "Admin {$username} menghapus tahun: " . htmlspecialchars($tahun_val));

            mysqli_commit($koneksi);
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Tahun " . htmlspecialchars($tahun_val) . " berhasil dihapus beserta datanya.</div>";
            header("Location: manage_documentsrevbaru.php");
            exit;
        } else {
            throw new Exception("Gagal menghapus data tahun dari database.");
        }
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        die("Terjadi kesalahan: " . $e->getMessage());
    }
} else {
    die("Request tidak valid.");
}
