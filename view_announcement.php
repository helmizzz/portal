<?php
require_once 'includes/init.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$announcement_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($announcement_id === 0) {
    header("Location: dashboard.php");
    exit();
}

$sql = "SELECT a.title, a.content, a.attachment_file, a.created_at, u.username AS author
        FROM announcements a
        LEFT JOIN users u ON a.created_by = u.id
        WHERE a.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $announcement_id);
$stmt->execute();
$result = $stmt->get_result();
$announcement = $result->fetch_assoc();

if (!$announcement) {
    die("Pengumuman tidak ditemukan atau Anda tidak memiliki akses.");
}

// --- LOGGING UNTUK AUDIT TRAIL SAAT PENGUMUMAN DILIHAT ---
// Cek agar aktivitas tidak tercatat berulang kali jika user me-refresh halaman dalam waktu singkat
$log_key = 'log_announcement_' . $announcement_id;
if (!isset($_SESSION[$log_key]) || (time() - $_SESSION[$log_key] > 300)) { // Cooldown 5 menit (300 detik)
    logActivity($conn, $_SESSION['user_id'], 'view_announcement', "Pengguna '{$_SESSION['username']}' melihat pengumuman: '" . htmlspecialchars($announcement['title']) . "'");
    $_SESSION[$log_key] = time();
}
// --- END LOGGING ---

$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);

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
<body>
    <?php include 'main_nav.php'; ?>

    <main class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="card-title mb-1"><?= htmlspecialchars($announcement['title']) ?></h2>
                <small class="text-muted">
                    Published by: <strong><?= htmlspecialchars($announcement['author'] ?? 'Sistem') ?></strong> 
                    on date <?= date('d F Y, H:i', strtotime($announcement['created_at'])) ?>
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
            <div class="card-footer text-end">
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </main>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>