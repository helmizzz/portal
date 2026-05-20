<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('manage_elearning', $permissions)) {
    header("Location: ../dashboard.php");
    exit();
}

$course_id = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;
if ($course_id === 0) {
    header("Location: manage_elearning.php");
    exit();
}

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Fetch Course Details
$stmt_course = $conn->prepare("SELECT title FROM elearning_courses WHERE id = ?");
$stmt_course->bind_param("i", $course_id);
$stmt_course->execute();
$course = $stmt_course->get_result()->fetch_assoc();
if (!$course) {
    die("Kursus tidak ditemukan.");
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_material') {
        $title = trim($_POST['title']);
        $content_type = $_POST['content_type'];
        $content_text = ($content_type === 'text') ? trim($_POST['content_text']) : null;
        $video_file_name = null;

        if ($content_type === 'video' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/elearning_videos/';
            if (!is_dir($upload_dir))
                mkdir($upload_dir, 0777, true);
            $file = $_FILES['video_file'];
            $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (in_array(strtolower($file_ext), ['mp4', 'webm', 'ogg'])) {
                $video_file_name = 'video_' . $course_id . '_' . uniqid() . '.' . $file_ext;
                move_uploaded_file($file['tmp_name'], $upload_dir . $video_file_name);
            } else {
                $_SESSION['flash_message'] = "<div class='alert alert-danger'>Format video tidak valid. Gunakan MP4, WEBM, atau OGG.</div>";
            }
        }

        if (!empty($title) && ($content_text !== null || $video_file_name !== null)) {
            $sql = "INSERT INTO elearning_materials (course_id, title, content_type, content_text, video_file_name) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issss", $course_id, $title, $content_type, $content_text, $video_file_name);
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "<div class='alert alert-success'>Materi berhasil ditambahkan.</div>";
            }
        } else {
            $_SESSION['flash_message'] = "<div class='alert alert-warning'>Judul dan konten harus diisi.</div>";
        }
    }

    header("Location: manage_materials.php?course_id=" . $course_id);
    exit();
}

// Handle GET actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    if ($_GET['action'] === 'delete') {
        $material_id = (int) $_GET['id'];
        // Optional: delete video file from server
        $stmt_file = $conn->prepare("SELECT video_file_name FROM elearning_materials WHERE id = ?");
        $stmt_file->bind_param("i", $material_id);
        $stmt_file->execute();
        $file_name = $stmt_file->get_result()->fetch_assoc()['video_file_name'];
        if ($file_name && file_exists('../uploads/elearning_videos/' . $file_name)) {
            unlink('../uploads/elearning_videos/' . $file_name);
        }

        $sql = "DELETE FROM elearning_materials WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $material_id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Materi berhasil dihapus.</div>";
        }
    }
    header("Location: manage_materials.php?course_id=" . $course_id);
    exit();
}


// Fetch all materials for the course
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
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Manage Materials</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="../assets/js/theme.js" defer></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable {
            min-height: 200px;
        }
    </style>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Manage Materials for: <span
                    class="text-primary"><?= htmlspecialchars($course['title']) ?></span></h2>
            <a href="manage_elearning.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Back to
                Courses</a>
        </div>

        <?= $message ?>
        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-header">Add New Material</div>
                    <div class="card-body">
                        <form action="manage_materials.php?course_id=<?= $course_id ?>" method="POST"
                            enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_material">
                            <div class="mb-3">
                                <label for="title" class="form-label">Material Title</label>
                                <input type="text" name="title" id="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="content_type" class="form-label">Content Type</label>
                                <select name="content_type" id="content_type" class="form-select">
                                    <option value="text">Text</option>
                                    <option value="video">Video</option>
                                </select>
                            </div>
                            <div id="text-input-group">
                                <label for="content_text" class="form-label">Text Content</label>
                                <textarea name="content_text" id="content_text" class="form-control"></textarea>
                            </div>
                            <div id="video-input-group" style="display: none;">
                                <label for="video_file" class="form-label">Upload Video (MP4, WebM, OGG)</label>
                                <input type="file" name="video_file" id="video_file" class="form-control"
                                    accept="video/mp4,video/webm,video/ogg">
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Add Material</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">Material List</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php if (count($materials) > 0):
                                foreach ($materials as $material): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i
                                                        class="fas fa-<?= $material['content_type'] === 'text' ? 'file-alt' : 'video' ?> me-2"></i>
                                                    <?= htmlspecialchars($material['title']) ?>
                                                </div>
                                                <a href="manage_materials.php?course_id=<?= $course_id ?>&action=delete&id=<?= $material['id'] ?>"
                                                    class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus materi ini?')"><i
                                                        class="fas fa-trash"></i></a>
                                            </li>
                                    <?php endforeach; else: ?>
                                    <li class="list-group-item text-center">Belum ada materi untuk kursus ini.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const contentTypeSelect = document.getElementById('content_type');
            const textGroup = document.getElementById('text-input-group');
            const videoGroup = document.getElementById('video-input-group');
            let editor;

            ClassicEditor.create(document.querySelector('#content_text')).then(e => editor = e);

            contentTypeSelect.addEventListener('change', function () {
                if (this.value === 'text') {
                    textGroup.style.display = 'block';
                    videoGroup.style.display = 'none';
                } else {
                    textGroup.style.display = 'none';
                    videoGroup.style.display = 'block';
                }
            });
        });
    </script>
</body>

</html>