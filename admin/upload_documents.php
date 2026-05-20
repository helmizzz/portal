<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (isset($_POST['upload'])) {
    $file_code = $_POST['file_code'] ?? '';
    $file_name = $_POST['file_name'] ?? '';
    $department_id = $_POST['department_id'] ?? '';
    $uploaded_by = $_SESSION['username'] ?? ''; // As per user query, linking by username
    $tahun_id = $_POST['tahun'] ?? ''; // This is the ID

    // Validation
    if (empty($file_code) || empty($file_name) || empty($department_id) || empty($_FILES['file']['name'])) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Semua field harus diisi!</div>";
        header("Location: manage_documentsrevbaru.php");
        exit();
    }

    // Get Department Name
    $stmt_thn = $conn->prepare("SELECT tahun FROM tahun WHERE id_tahun = ?");
    $stmt_thn->bind_param("i", $tahun_id);
    $stmt_thn->execute();
    $res_thn = $stmt_thn->get_result();
    $row_thn = $res_thn->fetch_assoc();
    $nama_tahun = $row_thn['tahun']; // This is the actual year (e.g. 2024)

    $stmt_dept = $conn->prepare("SELECT name FROM departements WHERE id = ?");
    $stmt_dept->bind_param("i", $department_id);
    $stmt_dept->execute();
    $res_dept = $stmt_dept->get_result();
    $row_dept = $res_dept->fetch_assoc();

    if (!$row_dept) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Departemen tidak valid!</div>";
        header("Location: manage_documentsrevbaru.php");
        exit();
    }
    $nama_dept = $row_dept['name'];

    // File Upload Handling
    $file = $_FILES['file'];
    $allowed_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Format file tidak diizinkan!</div>";
        header("Location: manage_documentsrevbaru.php");
        exit();
    }

    if ($file['size'] > 5000000) { // 5MB limit
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Ukuran file maksimal 5MB!</div>";
        header("Location: manage_documentsrevbaru.php");
        exit();
    }

    // --- PREVENTIF CEK 1: Apakah folder tahun/dept sudah dibuat di menu "Create Folder"? ---
    $stmt_check_f = $conn->prepare("SELECT id_fold FROM folder WHERE id_tahun = ? AND id_dep = ?");
    $stmt_check_f->bind_param("ii", $tahun_id, $department_id);
    $stmt_check_f->execute();
    if ($stmt_check_f->get_result()->num_rows === 0) {
        $_SESSION['flash_message'] = "<div class='alert alert-warning'>silahkan buat folder tahun/dept terlebih dahulu</div>";
        header("Location: manage_documentsrevbaru.php");
        exit();
    }

    // Sanitize file name from input
    $custom_name = preg_replace('/[^a-zA-Z0-9_\-]/', ' ', $file_name);
    $new_file_name = $custom_name . '.' . $file_ext;

    // --- PREVENTIF CEK 2: Apakah nama file sudah ada di database untuk tahun/dept ini? ---
    $stmt_check_d = $conn->prepare("SELECT id FROM documents WHERE file_name = ? AND tahun = ? AND nama_dept = ?");
    $stmt_check_d->bind_param("sss", $new_file_name, $nama_tahun, $nama_dept);
    $stmt_check_d->execute();
    if ($stmt_check_d->get_result()->num_rows > 0) {
        $_SESSION['flash_message'] = "<div class='alert alert-warning'>nama file sudah tersedia</div>";
        header("Location: manage_documentsrevbaru.php");
        exit();
    }



    // Prevent overwrite if file exists
    // Fix: Double quotes for variable interpolation
    $target_dir = "../uploads/{$nama_tahun}/{$nama_dept}/";

    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal membuat folder penyimpanan!</div>";
            header("Location: manage_documentsrevbaru.php");
            exit();
        }
    }

    $new_file_name = $custom_name . '.' . $file_ext;
    // (Note: duplicate check is now done via database query above)

    $upload_path = $target_dir . $new_file_name;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $sql = "INSERT INTO documents (file_code, file_name, nama_dept, uploaded_at, created_by, tahun, file_path) VALUES (?, ?, ?, NOW(), ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // Fix: Added 's' (or 'i') for the 5th parameter ($tahun). Assuming $tahun is string/int.
        // "sssss" matches 5 variables
        $stmt->bind_param("ssssss", $file_code, $new_file_name, $nama_dept, $uploaded_by, $nama_tahun, $upload_path);
        if ($stmt->execute()) {
            $new_doc_id = $conn->insert_id;
            $depts = $_POST['departements_access'] ?? [];
            $users = $_POST['users_access'] ?? [];
            update_access_rules($conn, $new_doc_id, 'document', $depts, $users);
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Dokumen berhasil diunggah!</div>";
        } else {
            $_SESSION['flash_message'] = "<div class='alert alert-danger'>Database Error: " . $stmt->error . "</div>";
        }
    } else {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal memindahkan file upload ke folder tujuan!</div>";
    }

    header("Location: manage_documentsrevbaru.php");
    exit();
}
?>