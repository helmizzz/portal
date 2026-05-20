<?php
require_once 'includes/init.php';
require_once 'includes/db_connect.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Token tidak valid.");
}

// Cari pengumuman berdasarkan token
$sql = "SELECT a.title, a.content, a.attachment_file, a.created_at, u.username AS author
        FROM announcements a
        JOIN public_announcements pa ON a.id = pa.announcement_id
        LEFT JOIN users u ON a.created_by = u.id
        WHERE pa.token = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$announcement = $result->fetch_assoc();

if (!$announcement) {
    die("Pengumuman tidak ditemukan atau tautan tidak valid.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($announcement['title']) ?></title>
    <link rel="icon" type="image/png" href="uploads/EIP.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body style="background-color: #f8f9fa;">
    <main class="container my-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h2 class="card-title mb-1"><?= htmlspecialchars($announcement['title']) ?></h2>
                <small>
                    Published by: <strong><?= htmlspecialchars($announcement['author'] ?? 'Sistem') ?></strong> 
                    on date <?= date('d F Y', strtotime($announcement['created_at'])) ?>
                </small>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <?= $announcement['content'] ?>
                </div>
                <?php if (!empty($announcement['attachment_file'])): ?>
                    <hr>
                    <h5>Attachment</h5>
                    <a href="uploads/attachments/<?= htmlspecialchars($announcement['attachment_file']) ?>" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-paperclip me-2"></i>
                        <?= htmlspecialchars($announcement['attachment_file']) ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>