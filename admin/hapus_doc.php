<?php
include '../includes/koneksi.php'; // koneksi DB
//include '../includes/db_connect.php';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'hapus_doc' &&
    isset($_POST['id'])
) {
    if (!isset($_SESSION['user_id'])) {
        die("Akses ditolak.");
    }

    $id = intval($_POST['id']);
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'Admin';

    // 1. Soft delete logic: change from DELETE to UPDATE is_active = 0
    // We do NOT unlink the file to allow restoration.
    $stmt2 = mysqli_prepare($koneksi, "UPDATE documents SET is_active = 0 WHERE id = ?");
    if ($stmt2) {
        mysqli_stmt_bind_param($stmt2, "i", $id);
        $success = mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        if ($success) {
            header("Location: manage_documentsrevbaru.php?status=success&msg=Data dan file berhasil dihapus");
            exit;
        } else {
            header("Location: manage_documentsrevbaru.php?status=error&msg=Gagal menghapus data");
            exit;
        }
    } else {
        header("Location: manage_documentsrevbaru.php?status=error&msg=Gagal mempersiapkan query");
        exit;
    }

} else {
    header("Location: manage_documentsrevbaru.php?status=error&msg=ID tidak ditemukan");
    exit;
}
?>