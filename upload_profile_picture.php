<?php
require_once 'includes/init.php';
session_start();
require_once 'includes/db_connect.php';

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_message'] = "<div class='alert alert-danger'>Anda harus login untuk mengubah foto profil.</div>";
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$upload_dir = 'uploads/profiles/';
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_size = 2 * 1024 * 1024; // 2 MB

if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_pic'];

    // 1. Validasi Ukuran File
    if ($file['size'] > $max_size) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Ukuran file terlalu besar. Maksimal 2 MB.</div>";
        header("Location: dashboard.php");
        exit();
    }

    // 2. Validasi Tipe File
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    if (!in_array($mime_type, $allowed_types)) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Tipe file tidak valid. Hanya JPG, PNG, dan GIF yang diizinkan.</div>";
        header("Location: dashboard.php");
        exit();
    }

    // 3. Buat nama file unik untuk menghindari konflik
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'user_' . $user_id . '_' . time() . '.' . $extension;
    $destination = $upload_dir . $new_filename;

    // 4. Hapus foto profil lama jika ada
    $sql_old_pic = "SELECT profile_picture FROM users WHERE id = ?";
    $stmt_old = $conn->prepare($sql_old_pic);
    $stmt_old->bind_param("i", $user_id);
    $stmt_old->execute();
    $result_old = $stmt_old->get_result()->fetch_assoc();
    if ($result_old && !empty($result_old['profile_picture'])) {
        $old_file_path = $upload_dir . $result_old['profile_picture'];
        if (file_exists($old_file_path)) {
            unlink($old_file_path); // Hapus file lama dari server
        }
    }

    // 5. Pindahkan file baru dan update database
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        $sql_update = "UPDATE users SET profile_picture = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $new_filename, $user_id);

        if ($stmt_update->execute()) {
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Foto profil berhasil diperbarui.</div>";
        } else {
            $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal memperbarui database.</div>";
        }
    } else {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal memindahkan file yang diunggah.</div>";
    }
} else {
    $_SESSION['flash_message'] = "<div class='alert alert-danger'>Terjadi kesalahan saat mengunggah atau tidak ada file yang dipilih.</div>";
}

header("Location: dashboard.php");
exit();