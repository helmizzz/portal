<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/koneksi.php';

if (isset($_POST['upload'])) {
    $file_code = $_POST['file_code'] ?? '';
    $file_name = $_POST['file_name'] ?? '';
    $department_id = $_POST['department_id'] ?? '';
    $uploaded_by = $_SESSION['username'] ?? ''; // As per user query, linking by username

    // Validation
    if (empty($file_code) || empty($file_name) || empty($department_id) || empty($_FILES['file']['name'])) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Semua field harus diisi!</div>";
        header("Location: manage_documents.php");
        exit();
    }

    // Get Department Name
    $stmt_dept = $conn->prepare("SELECT name FROM departements WHERE id = ?");
    $stmt_dept->bind_param("i", $department_id);
    $stmt_dept->execute();
    $res_dept = $stmt_dept->get_result();
    $row_dept = $res_dept->fetch_assoc();

    if (!$row_dept) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Departemen tidak valid!</div>";
        header("Location: manage_documents.php");
        exit();
    }
    $nama_dept = $row_dept['name'];

    // File Upload Handling
    $file = $_FILES['file'];
    $allowed_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Format file tidak diizinkan!</div>";
        header("Location: manage_documents.php");
        exit();
    }

    if ($file['size'] > 5000000) { // 5MB limit
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Ukuran file maksimal 5MB!</div>";
        header("Location: manage_documents.php");
        exit();
    }

    // Sanitize file name from input
    $custom_name = preg_replace('/[^a-zA-Z0-9_\-]/', ' ', $file_name);
    $new_file_name = $custom_name . '.' . $file_ext;

    // Prevent overwrite if file exists
    if (file_exists('../uploads/' . $new_file_name)) {
        $new_file_name = $custom_name . '.' . $file_ext;
    }
    $upload_path = '../uploads/' . $new_file_name;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $sql = "INSERT INTO documents (file_code, file_name, nama_dept, uploaded_at, created_by) VALUES (?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $file_code, $new_file_name, $nama_dept, $uploaded_by);

        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Dokumen berhasil diunggah!</div>";
        } else {
            $_SESSION['flash_message'] = "<div class='alert alert-danger'>Database Error: " . $stmt->error . "</div>";
        }
    } else {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal memindahkan file upload!</div>";
    }

    header("Location: manage_documents.php");
    exit();
}
?>