<?php
require_once 'includes/init.php';
require_once 'includes/theme_handler.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$user_permissions = getUserPermissions($conn, $user_id);

$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
if ($course_id === 0) {
    header("Location: elearning.php");
    exit();
}

// Fetch course and materials
$stmt_course = $conn->prepare("SELECT title, description FROM elearning_courses WHERE id = ?");
$stmt_course->bind_param("i", $course_id);
$stmt_course->execute();
$course = $stmt_course->get_result()->fetch_assoc();
if (!$course) { die("Kursus tidak ditemukan."); }

$materials = [];
$sql_materials = "SELECT * FROM elearning_materials WHERE course_id = ? ORDER BY sort_order ASC, id ASC";
$stmt_materials = $conn->prepare($sql_materials);
$stmt_materials->bind_param("i", $course_id);
$stmt_materials->execute();
$result_materials = $stmt_materials->get_result();
$material_ids = [];
while ($row = $result_materials->fetch_assoc()) {
    $materials[] = $row;
    $material_ids[] = $row['id'];
}

// Fetch completion status
$completed_materials = [];
if (count($material_ids) > 0) {
    $in_clause = implode(',', array_fill(0, count($material_ids), '?'));
    $sql_completion = "SELECT material_id FROM elearning_completion WHERE user_id = ? AND material_id IN ($in_clause)";
    $stmt_completion = $conn->prepare($sql_completion);
    $params = array_merge([$user_id], $material_ids);
    $types = 'i' . str_repeat('i', count($material_ids));
    $stmt_completion->bind_param($types, ...$params);
    $stmt_completion->execute();
    $result_completion = $stmt_completion->get_result();
    while ($row = $result_completion->fetch_assoc()) {
        $completed_materials[] = $row['material_id'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']) ?></title>
    <link rel="icon" type="image/png" href="uploads/EIP.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .material-video { max-width: 100%; border-radius: .375rem; }
    </style>
</head>
<body class="<?= $theme_class ?>">
    <?php include 'main_nav.php'; ?>
    <main class="container mt-4">
        <h2 class="mb-2"><?= htmlspecialchars($course['title']) ?></h2>
        <p class="text-muted"><?= htmlspecialchars($course['description']) ?></p>
        <hr>

        <div class="accordion" id="materialsAccordion">
            <?php foreach ($materials as $index => $material):
                $is_completed = in_array($material['id'], $completed_materials);
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading-<?= $material['id'] ?>">
                    <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $material['id'] ?>">
                        <i class="fas fa-<?= $is_completed ? 'check-circle text-success' : 'circle' ?> me-2"></i>
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
                        
                        <hr>
                        <button class="btn btn-<?= $is_completed ? 'success' : 'outline-primary' ?> mark-complete-btn" data-material-id="<?= $material['id'] ?>" <?= $is_completed ? 'disabled' : '' ?>>
                            <i class="fas fa-check me-1"></i> <?= $is_completed ? 'Telah Selesai' : 'Tandai Selesai' ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="elearning.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Kursus</a>
        </div>
    </main>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.mark-complete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const materialId = this.dataset.materialId;
                const btn = this;
                
                fetch('api/mark_elearning_complete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ material_id: materialId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        btn.textContent = 'Telah Selesai';
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-success');
                        btn.disabled = true;

                        // Update icon on accordion header
                        const headerIcon = document.querySelector(`#heading-${materialId} .fas`);
                        headerIcon.classList.remove('fa-circle');
                        headerIcon.classList.add('fa-check-circle', 'text-success');
                    } else {
                        alert('Gagal menandai selesai: ' + data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });
    });
    </script>
</body>
</html>