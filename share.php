<?php
require_once 'includes/init.php';
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once 'includes/db_connect.php';

$error = '';
$link_data = null;
$show_pdf = false;

if (!isset($_GET['token'])) {
    $error = 'Tautan tidak valid atau tidak lengkap.';
} else {
    $token = $_GET['token'];
    $stmt = $conn->prepare("SELECT * FROM shared_links WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $error = 'Tautan berbagi tidak ditemukan.';
    } else {
        $link_data = $result->fetch_assoc();
        
        // Cek tanggal kedaluwarsa
        if ($link_data['expires_at'] && new DateTime() > new DateTime($link_data['expires_at'])) {
            $error = 'Tautan ini telah kedaluwarsa.';
            $link_data = null; // Anggap link tidak valid
        } else {
            // Cek password
            if ($link_data['password']) {
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
                    if (password_verify($_POST['password'], $link_data['password'])) {
                        $show_pdf = true; // Password benar, tampilkan PDF
                    } else {
                        $error = 'Password salah.';
                    }
                }
                // Jika belum submit password, jangan tampilkan PDF
            } else {
                $show_pdf = true; // Tidak ada password, langsung tampilkan PDF
            }
        }
    }
}

$pdf_url = '';
if ($show_pdf && $link_data) {
    $doc_stmt = $conn->prepare("SELECT file_name FROM documents WHERE id = ?");
    $doc_stmt->bind_param("i", $link_data['document_id']);
    $doc_stmt->execute();
    $doc_result = $doc_stmt->get_result()->fetch_assoc();
    if ($doc_result) {
        // Asumsi path relatif dari root proyek
        $pdf_path = 'uploads/' . $doc_result['file_name'];
        $pdf_viewer_url = 'assets/pdfjs/web/viewer.html?file=' . urlencode('/iportal/' . $pdf_path);
    } else {
        $error = 'File dokumen yang terkait dengan tautan ini tidak ditemukan.';
        $show_pdf = false;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Documents Shared</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        html, body { height: 100%; margin: 0; padding: 0; font-family: sans-serif; }
        .container-center { display: flex; align-items: center; justify-content: center; min-height: 100vh; background-color: #f0f2f5; }
        .card { min-width: 350px; }
        iframe { width: 100vw; height: 100vh; border: none; }
    </style>
</head>
<body>
    <?php if ($show_pdf && !empty($pdf_viewer_url)): ?>
        <iframe src="<?= htmlspecialchars($pdf_viewer_url) ?>"></iframe>
    <?php else: ?>
        <div class="container-center">
            <div class="card p-4 shadow-sm">
                <h4 class="text-center mb-3">Document Access</h4>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($link_data && $link_data['password']): ?>
                    <p>This document is password protected.</p>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="password" class="form-label">Enter Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Open Document</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>