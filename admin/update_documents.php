<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (isset($_POST['updateDocument'])) {
    $doc_id = $_POST['doc_id'] ?? '';
    $file_code = $_POST['file_code'] ?? '';
    $file_name_input = $_POST['file_name'] ?? ''; // Display name
    $department_id = $_POST['department_id'] ?? '';
    $tahun_id = $_POST['tahun'] ?? '';
    $uploaded_by = $_SESSION['username'] ?? '';

    // Validation
    if (empty($doc_id) || empty($file_code) || empty($file_name_input) || empty($department_id) || empty($tahun_id)) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Semua field harus diisi!</div>";
        header("Location: manage_documentsrevbaru.php");
        exit();
    }

    // Get Original Data for potential file move/cleanup
    $stmt_orig = $conn->prepare("SELECT file_name, file_path, tahun, nama_dept FROM documents WHERE id = ?");
    $stmt_orig->bind_param("i", $doc_id);
    $stmt_orig->execute();
    $res_orig = $stmt_orig->get_result();
    $orig_data = $res_orig->fetch_assoc();

    if (!$orig_data) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Dokumen tidak ditemukan!</div>";
        header("Location: manage_documentsrevbaru.php");
        exit();
    }

    // Get Target Department & Year Names
    $stmt_thn = $conn->prepare("SELECT tahun FROM tahun WHERE id_tahun = ?");
    $stmt_thn->bind_param("i", $tahun_id);
    $stmt_thn->execute();
    $res_thn = $stmt_thn->get_result();
    $row_thn = $res_thn->fetch_assoc();
    $nama_tahun = $row_thn['tahun'];

    $stmt_dept = $conn->prepare("SELECT name FROM departements WHERE id = ?");
    $stmt_dept->bind_param("i", $department_id);
    $stmt_dept->execute();
    $res_dept = $stmt_dept->get_result();
    $row_dept = $res_dept->fetch_assoc();
    $nama_dept = $row_dept['name'];

    // Check if Year/Dept folder combination exists in 'folder' table
    $stmt_check_f = $conn->prepare("SELECT id_fold FROM folder WHERE id_tahun = ? AND id_dep = ?");
    $stmt_check_f->bind_param("ii", $tahun_id, $department_id);
    $stmt_check_f->execute();
    if ($stmt_check_f->get_result()->num_rows === 0) {
        $_SESSION['flash_message'] = "<div class='alert alert-warning'>Silahkan buat folder tahun/dept terlebih dahulu di menu Tambah Folder</div>";
        header("Location: manage_documentsrevbaru.php");
        exit();
    }

    $target_dir = "../uploads/{$nama_tahun}/{$nama_dept}/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $final_file_name = $orig_data['file_name'];
    $final_file_path = $orig_data['file_path'];

    // Handle File Replacement OR Rename
    if (!empty($_FILES['file']['name'])) {
        // NEW FILE UPLOADED
        $file = $_FILES['file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Sanitize name
        $custom_name = preg_replace('/[^a-zA-Z0-9_\-]/', ' ', $file_name_input);
        $new_file_name = $custom_name . '.' . $file_ext;
        $new_file_path = $target_dir . $new_file_name;

        if (move_uploaded_file($file['tmp_name'], $new_file_path)) {
            // Delete old file if it's different
            if ($orig_data['file_path'] && file_exists($orig_data['file_path']) && $orig_data['file_path'] !== $new_file_path) {
                unlink($orig_data['file_path']);
            }
            $final_file_name = $new_file_name;
            $final_file_path = $new_file_path;
        }
    } else {
        // NO NEW FILE - Check if we need to move the old file due to Year/Dept/Name change
        $custom_name = preg_replace('/[^a-zA-Z0-9_\-]/', ' ', $file_name_input);
        $file_ext = pathinfo($orig_data['file_name'], PATHINFO_EXTENSION);
        $new_file_name = $custom_name . '.' . $file_ext;
        $new_file_path = $target_dir . $new_file_name;

        if ($orig_data['file_path'] !== $new_file_path) {
            if (file_exists($orig_data['file_path'])) {
                rename($orig_data['file_path'], $new_file_path);
            }
            $final_file_name = $new_file_name;
            $final_file_path = $new_file_path;
        }
    }

    // Update Database
    $sql = "UPDATE documents SET 
            file_code = ?, 
            file_name = ?, 
            nama_dept = ?, 
            tahun = ?, 
            file_path = ?,
            updated_at = NOW()
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $file_code, $final_file_name, $nama_dept, $nama_tahun, $final_file_path, $doc_id);

    if ($stmt->execute()) {
        $depts = $_POST['departements_access'] ?? [];
        $users = $_POST['users_access'] ?? [];
        update_access_rules($conn, $doc_id, 'document', $depts, $users);
        $_SESSION['flash_message'] = "<div class='alert alert-success'>Dokumen berhasil diperbarui!</div>";
    } else {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal memperbarui database: " . $stmt->error . "</div>";
    }

    header("Location: manage_documentsrevbaru.php");
    exit();
}
?>