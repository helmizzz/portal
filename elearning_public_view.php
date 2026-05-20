<?php
require_once 'includes/init.php';
require_once 'includes/db_connect.php';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("Tautan tidak valid atau tidak lengkap.");
}

// Fetch course and materials
$stmt_course = $conn->prepare("SELECT id, title, description FROM elearning_courses WHERE share_token = ?");
$stmt_course->bind_param("s", $token);
$stmt_course->execute();
$course = $stmt_course->get_result()->fetch_assoc();
if (!$course) { die("Kursus tidak ditemukan atau tautan salah."); }

$course_id = $course['id'];
$materials = [];
$sql_materials = "SELECT * FROM elearning_materials WHERE course_id = ? ORDER BY sort_order ASC, id ASC";
$stmt_materials = $conn->prepare($sql_materials);
$stmt_materials->bind_param("i", $course_id);
$stmt_materials->execute();
$result_materials = $stmt_materials->get_result();
while ($row = $result_materials->fetch_assoc()) {
    $materials[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learning: <?= htmlspecialchars($course['title']) ?></title>
    <link rel="icon" type="image/png" href="uploads/EIP.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style> body { background-color: #f0f2f5; } .material-video { max-width: 100%; border-radius: .375rem; } </style>
</head>
<body>
    <main class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h2 class="card-title mb-1"><?= htmlspecialchars($course['title']) ?></h2>
            </div>
            <div class="card-body">
                <p class="text-muted"><?= htmlspecialchars($course['description']) ?></p>
                <hr>
                <div class="accordion" id="materialsAccordion">
                    <?php foreach ($materials as $index => $material): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-<?= $material['id'] ?>">
                            <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $material['id'] ?>">
                                <?= htmlspecialchars($material['title']) ?>
                            </button>
                        </h2>
                        <div id="collapse-<?= $material['id'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#materialsAccordion">
                            <div class="accordion-body">
                                <?php if ($material['content_type'] === 'text'): ?>
                                    <div><?= $material['content_text'] ?></div>
                                <?php elseif ($material['content_type'] === 'video' && $material['video_file_name']): ?>
                                    <video class="material-video" controls>
                                        <source src="uploads/elearning_videos/<?= $material['video_file_name'] ?>" type="video/mp4">
                                        Browser Anda tidak mendukung tag video.
                                    </video>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
         <div class="text-center mt-3">
            <small class="text-muted">Powered by Information Portal</small>
        </div>
    </main>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>