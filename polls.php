<?php
require_once 'includes/init.php';
require_once 'includes/theme_handler.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);

// Ambil semua polling yang aktif
$polls = [];
$sql = "SELECT id, title, description, created_at FROM polls WHERE status = 'active' ORDER BY created_at DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $polls[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Poll List</title>
    <link rel="icon" type="image/png" href="uploads/EIP.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= $theme_class ?>">
    <?php include 'main_nav.php'; ?>
    <main class="container mt-4">
        <h2 class="mb-4">List of Active Polls and Surveys</h2>

        <?php if (count($polls) > 0): ?>
            <div class="list-group">
            <?php foreach ($polls as $poll): ?>
                <a href="poll_view.php?id=<?= $poll['id'] ?>" class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?= htmlspecialchars($poll['title']) ?></h5>
                        <small>Published on <?= date('d M Y', strtotime($poll['created_at'])) ?></small>
                    </div>
                    <p class="mb-1"><?= htmlspecialchars($poll['description'] ?: 'Klik untuk mengikuti polling ini.') ?></p>
                </a>
            <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                There are currently no active polls or surveys.
            </div>
        <?php endif; ?>
    </main>
</body>
</html>