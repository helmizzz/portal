<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('manage_elearning', $user_permissions) && $_SESSION['role'] !== 'Admin') {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
    header("Location: ../dashboard.php"); // Atau kembali ke dashboard jika login tapi tidak punya akses
    exit();
}


$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
$admin_id = $_SESSION['user_id'];

// Logika POST untuk menambah kursus dan materi sekaligus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_course') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $materials = $_POST['materials'] ?? [];

    if (empty($title) || empty($materials)) {
        $_SESSION['flash_message'] = "<div class='alert alert-warning'>Course title and at least one material must be filled in.</div>";
        header("Location: manage_elearning.php");
        exit();
    }

    $conn->begin_transaction();
    try {
        // 1. Masukkan data kursus utama
        $sql_course = "INSERT INTO elearning_courses (title, description, created_by) VALUES (?, ?, ?)";
        $stmt_course = $conn->prepare($sql_course);
        $stmt_course->bind_param("ssi", $title, $description, $admin_id);
        $stmt_course->execute();
        $course_id = $conn->insert_id;

        $sql_material = "INSERT INTO elearning_materials (course_id, title, content_type, content_text, video_file_name, sort_order) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_material = $conn->prepare($sql_material);

        $order = 0;
        foreach ($materials as $key => $material_data) {
            $material_title = trim($material_data['title']);
            $content_type = $material_data['content_type'];
            $content_text = ($content_type === 'text') ? trim($material_data['content_text']) : null;
            $video_file_name = null;

            if (empty($material_title))
                continue; // Lewati materi tanpa judul

            // 2. Handle upload video jika ada
            if ($content_type === 'video' && isset($_FILES['materials']['error'][$key]['video_file']) && $_FILES['materials']['error'][$key]['video_file'] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['materials']['name'][$key]['video_file'],
                    'tmp_name' => $_FILES['materials']['tmp_name'][$key]['video_file'],
                ];
                $upload_dir = '../uploads/elearning_videos/';
                if (!is_dir($upload_dir))
                    mkdir($upload_dir, 0777, true);

                $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                if (in_array(strtolower($file_ext), ['mp4', 'webm', 'ogg'])) {
                    $video_file_name = 'video_' . $course_id . '_' . uniqid() . '.' . $file_ext;
                    move_uploaded_file($file['tmp_name'], $upload_dir . $video_file_name);
                } else {
                    throw new Exception("Invalid video format on material '$material_title'. Use MP4, WEBM, atau OGG.");
                }
            }

            if ($content_type === 'text' && empty($content_text))
                continue;
            if ($content_type === 'video' && empty($video_file_name))
                continue;

            // 3. Masukkan data materi
            $stmt_material->bind_param("issssi", $course_id, $material_title, $content_type, $content_text, $video_file_name, $order);
            $stmt_material->execute();
            $order++;
        }

        $conn->commit();
        create_notification($conn, 'all', "A new E-Learning course has been published: '" . htmlspecialchars($title) . "'", "elearning_view.php?course_id=" . $course_id, $admin_id);
        $_SESSION['flash_message'] = "<div class='alert alert-success'>The course and its materials have been successfully created.</div>";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>There is an error: " . $e->getMessage() . "</div>";
    }

    header("Location: manage_elearning.php");
    exit();
}


// Logika GET (Hapus & Bagikan) - Tetap sama
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if ($_GET['action'] === 'delete') {
        // Hapus juga file video terkait
        $res_videos = $conn->query("SELECT video_file_name FROM elearning_materials WHERE course_id = $id AND video_file_name IS NOT NULL");
        while ($row = $res_videos->fetch_assoc()) {
            if (file_exists('../uploads/elearning_videos/' . $row['video_file_name'])) {
                unlink('../uploads/elearning_videos/' . $row['video_file_name']);
            }
        }
        $sql = "DELETE FROM elearning_courses WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "<div class='alert alert-success'>The course was successfully deleted.</div>";
        }
    } elseif ($_GET['action'] === 'share') {
        $stmt_check = $conn->prepare("SELECT share_token FROM elearning_courses WHERE id = ?");
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $token = $stmt_check->get_result()->fetch_assoc()['share_token'];
        if (empty($token)) {
            $token = bin2hex(random_bytes(16));
            $stmt_update = $conn->prepare("UPDATE elearning_courses SET share_token = ? WHERE id = ?");
            $stmt_update->bind_param("si", $token, $id);
            $stmt_update->execute();
        }
        $link_scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $link_host = $_SERVER['HTTP_HOST'];
        $link_path = dirname($_SERVER['PHP_SELF'], 2) . "/elearning_public_view.php?token=" . $token;
        $full_link = "{$link_scheme}://{$link_host}{$link_path}";
        $_SESSION['flash_message'] = "<div class='alert alert-info'>Share this link: <input type='text' class='form-control mt-2' value='" . htmlspecialchars($full_link) . "' readonly onclick='this.select()'></div>";
    }
    header("Location: manage_elearning.php");
    exit();
}

// Ambil semua kursus
$courses = [];
$sql = "SELECT c.*, (SELECT COUNT(*) FROM elearning_materials WHERE course_id = c.id) as material_count 
        FROM elearning_courses c ORDER BY c.created_at DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>E-Learning Management</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="../assets/js/theme.js" defer></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <style>
        .material-block {
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }

        body.dark-mode .material-block {
            border-color: #444;
            background-color: #2a2a2a;
        }

        .ck-editor__editable {
            min-height: 150px;
        }
    </style>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <h2>E-Learning Management</h2>
        <?= $message ?>
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card">
                    <div class="card-header">Create New Course</div>
                    <div class="card-body">
                        <form action="manage_elearning.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_course">
                            <div class="mb-3">
                                <label for="title" class="form-label">Course Title</label>
                                <input type="text" name="title" id="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                            </div>
                            <hr>
                            <h5 class="mb-3">Course Materials</h5>
                            <div id="materials-container">
                            </div>
                            <button type="button" id="add-material-btn" class="btn btn-success mt-2"><i
                                    class="fas fa-plus"></i> Add Material</button>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Publish Course</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">Course List</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Materials</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($courses) > 0):
                                        foreach ($courses as $course): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($course['title']) ?></td>
                                                        <td><?= $course['material_count'] ?></td>
                                                        <td>
                                                            <a href="../elearning_view.php?course_id=<?= $course['id'] ?>"
                                                                class="btn btn-success btn-sm" title="Lihat Kursus" target="_blank"><i
                                                                    class="fas fa-eye"></i></a>
                                                            <a href="manage_elearning.php?action=share&id=<?= $course['id'] ?>"
                                                                class="btn btn-info btn-sm" title="Bagikan"><i
                                                                    class="fas fa-share-alt"></i></a>
                                                            <a href="manage_elearning.php?action=delete&id=<?= $course['id'] ?>"
                                                                class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Yakin hapus kursus ini? Semua materi di dalamnya akan ikut terhapus.')"
                                                                title="Hapus Kursus"><i class="fas fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                            <?php endforeach; else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Belum ada kursus yang dibuat.</td>
                                            </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let materialCounter = 0;
            const materialsContainer = document.getElementById('materials-container');

            const addMaterial = () => {
                materialCounter++;
                const materialId = `m-${materialCounter}`;
                const materialBlock = document.createElement('div');
                materialBlock.className = 'material-block';
                materialBlock.id = materialId;
                materialBlock.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0"><strong>Materi #${materialCounter}</strong></label>
                    <button type="button" class="btn-close remove-material-btn"></button>
                </div>
                <div class="mb-2">
                    <input type="text" name="materials[${materialId}][title]" class="form-control" placeholder="Judul Materi" required>
                </div>
                <div class="mb-2">
                    <select name="materials[${materialId}][content_type]" class="form-select content-type-select">
                        <option value="text" selected>Text</option>
                        <option value="video">Video</option>
                    </select>
                </div>
                <div class="text-input-group">
                    <textarea name="materials[${materialId}][content_text]" class="form-control ck-editor"></textarea>
                </div>
                <div class="video-input-group" style="display: none;">
                    <input type="file" name="materials[${materialId}][video_file]" class="form-control" accept="video/mp4,video/webm,video/ogg">
                </div>
            `;
                materialsContainer.appendChild(materialBlock);

                // Inisialisasi CKEditor untuk textarea baru
                ClassicEditor.create(materialBlock.querySelector('.ck-editor')).catch(err => console.error(err));
            };

            document.getElementById('add-material-btn').addEventListener('click', addMaterial);

            materialsContainer.addEventListener('click', function (e) {
                // Hapus Materi
                if (e.target.classList.contains('remove-material-btn')) {
                    e.target.closest('.material-block').remove();
                }
            });

            materialsContainer.addEventListener('change', function (e) {
                // Ganti tipe konten
                if (e.target.classList.contains('content-type-select')) {
                    const block = e.target.closest('.material-block');
                    const textGroup = block.querySelector('.text-input-group');
                    const videoGroup = block.querySelector('.video-input-group');
                    if (e.target.value === 'text') {
                        textGroup.style.display = 'block';
                        videoGroup.style.display = 'none';
                    } else {
                        textGroup.style.display = 'none';
                        videoGroup.style.display = 'block';
                    }
                }
            });

            // Tambah satu materi secara default saat halaman dimuat
            addMaterial();
        });
    </script>
</body>

</html>