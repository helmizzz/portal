<?php
require_once '../includes/theme_handler.php';
$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$admin_id = $_SESSION['user_id'];
$admin_username = $_SESSION['username'];

function handle_attachment_upload($file_input_name)
{
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/attachments/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file = $_FILES[$file_input_name];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $max_size = 5 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            return ['error' => 'Ukuran file terlalu besar. Maksimal 5 MB.'];
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($file['tmp_name']);
        if (!in_array($mime_type, $allowed_types)) {
            return ['error' => 'Tipe file tidak valid. Hanya JPG, PNG, GIF, dan PDF yang diizinkan.'];
        }
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'attachment_' . time() . '_' . uniqid() . '.' . $extension;
        $destination = $upload_dir . $new_filename;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => $new_filename];
        } else {
            return ['error' => 'Failed to move attached file.'];
        }
    }
    return ['success' => null];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $action_success = false;
    $success_message = '';
    $error_message = '';

    if ($action === 'add_announcement') {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $attachment_result = handle_attachment_upload('attachment');

        if (isset($attachment_result['error'])) {
            $error_message = "<div class='alert alert-danger'>" . $attachment_result['error'] . "</div>";
        } elseif (!empty($title) && !empty($content)) {
            $attachment_file = $attachment_result['success'];
            $sql = "INSERT INTO announcements (title, content, attachment_file, start_date, end_date, is_pinned, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssii", $title, $content, $attachment_file, $start_date, $end_date, $is_pinned, $admin_id);
            if ($stmt->execute()) {
                $action_success = true;
                $success_message = "<div class='alert alert-success'>Pengumuman berhasil ditambahkan.</div>";
                logActivity($conn, $admin_id, 'create_announcement', "Admin '{$admin_username}' membuat pengumuman: '" . htmlspecialchars($title) . "'");
            } else {
                $error_message = "<div class='alert alert-danger'>Gagal menambahkan pengumuman.</div>";
            }
        }
    } elseif ($action === 'edit_announcement') {
        $id = (int) $_POST['announcement_id'];
        $title = trim($_POST['edit_title']);
        $content = trim($_POST['edit_content']);
        $is_pinned = isset($_POST['edit_is_pinned']) ? 1 : 0;
        $start_date = !empty($_POST['edit_start_date']) ? $_POST['edit_start_date'] : null;
        $end_date = !empty($_POST['edit_end_date']) ? $_POST['edit_end_date'] : null;
        $attachment_file = $_POST['current_attachment'];

        if (isset($_FILES['edit_attachment']) && $_FILES['edit_attachment']['error'] === UPLOAD_ERR_OK) {
            $attachment_result = handle_attachment_upload('edit_attachment');
            if (isset($attachment_result['error'])) {
                $error_message = "<div class='alert alert-danger'>" . $attachment_result['error'] . "</div>";
            } else {
                if (!empty($attachment_file) && file_exists('../uploads/attachments/' . $attachment_file)) {
                    unlink('../uploads/attachments/' . $attachment_file);
                }
                $attachment_file = $attachment_result['success'];
            }
        }

        if (empty($error_message)) {
            $sql = "UPDATE announcements SET title = ?, content = ?, attachment_file = ?, start_date = ?, end_date = ?, is_pinned = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssii", $title, $content, $attachment_file, $start_date, $end_date, $is_pinned, $id);
            if ($stmt->execute()) {
                $action_success = true;
                $success_message = "<div class='alert alert-success'>Pengumuman berhasil diperbarui.</div>";
                logActivity($conn, $admin_id, 'edit_announcement', "Admin '{$admin_username}' mengedit pengumuman: '" . htmlspecialchars($title) . "' (ID: {$id})");
            } else {
                $error_message = "<div class='alert alert-danger'>Gagal memperbarui pengumuman.</div>";
            }
        }
    }

    if ($action_success)
        $_SESSION['flash_message'] = $success_message;
    else
        $_SESSION['flash_message'] = $error_message ?: "<div class='alert alert-danger'>Terjadi kesalahan.</div>";
    header("Location: manage_announcements.php");
    exit();
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    if ($_GET['action'] === 'delete') {
        $stmt_data = $conn->prepare("SELECT title, attachment_file FROM announcements WHERE id = ?");
        $stmt_data->bind_param("i", $id);
        $title_to_log = "ID: {$id}";
        if ($stmt_data->execute()) {
            $result_data = $stmt_data->get_result();
            if ($row_data = $result_data->fetch_assoc()) {
                $title_to_log = $row_data['title'];
                if (!empty($row_data['attachment_file']) && file_exists('../uploads/attachments/' . $row_data['attachment_file'])) {
                    unlink('../uploads/attachments/' . $row_data['attachment_file']);
                }
            }
        }
        $sql = "DELETE FROM announcements WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Announcement successfully deleted.</div>";
            logActivity($conn, $admin_id, 'delete_announcement', "Admin '{$admin_username}' delete announcement: '" . htmlspecialchars($title_to_log) . "'");
        } else {
            $_SESSION['flash_message'] = "<div class='alert alert-danger'>Failed to delete announcement.</div>";
        }
    } elseif ($_GET['action'] === 'share') {
        $stmt_check = $conn->prepare("SELECT token FROM public_announcements WHERE announcement_id = ?");
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($row = $result_check->fetch_assoc()) {
            $token = $row['token'];
        } else {
            $token = bin2hex(random_bytes(16));
            $stmt_insert = $conn->prepare("INSERT INTO public_announcements (announcement_id, token) VALUES (?, ?)");
            $stmt_insert->bind_param("is", $id, $token);
            $stmt_insert->execute();
        }

        $link_scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $link_host = $_SERVER['HTTP_HOST'];
        $link_path = dirname($_SERVER['PHP_SELF'], 2) . "/public_view.php?token=" . $token;
        $full_link = "{$link_scheme}://{$link_host}{$link_path}";

        $_SESSION['flash_message'] = "<div class='alert alert-success'>Public link successfully created. Copy the link below:<input type='text' class='form-control mt-2' value='" . htmlspecialchars($full_link) . "' readonly onclick='this.select()'></div>";
    }

    header("Location: manage_announcements.php");
    exit();
}

$announcements = [];
$sql = "SELECT a.id, a.title, a.content, a.attachment_file, a.start_date, a.end_date, a.is_pinned, a.created_at, u.username 
        FROM announcements a 
        LEFT JOIN users u ON a.created_by = u.id 
        ORDER BY a.is_pinned DESC, a.created_at DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Announcement Management</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script src="../assets/js/theme.js" defer></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .ck-editor__editable {
            min-height: 250px;
        }
    </style>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <h2>Announcement Management</h2>
        <?= $message ?>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">Add New Announcement</div>
                    <div class="card-body">
                        <form action="manage_announcements.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_announcement">
                            <div class="mb-3"><label for="title" class="form-label">Title</label><input type="text"
                                    name="title" id="title" class="form-control" required></div>
                            <div class="mb-3"><label for="content" class="form-label">Announcement
                                    Contents</label><textarea name="content" id="content"
                                    class="form-control"></textarea></div>
                            <div class="mb-3"><label for="attachment" class="form-label">Attachments
                                    (Optional)</label><input class="form-control" type="file" name="attachment"
                                    id="attachment"></div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="datetime-local" name="start_date" id="start_date" class="form-control">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="datetime-local" name="end_date" id="end_date" class="form-control">
                                </div>
                            </div>
                            <div class="mb-3 form-check"><input type="checkbox" name="is_pinned" id="is_pinned"
                                    class="form-check-input" value="1"><label for="is_pinned"
                                    class="form-check-label">Pin Announcement</label></div>
                            <button type="submit" class="btn btn-primary">Publish</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">Announcement List</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Validity period</th>
                                        <th>Made by</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($announcements) > 0):
                                        foreach ($announcements as $ann): ?>
                                                    <tr>
                                                        <td>
                                                            <?= htmlspecialchars($ann['title']) ?>
                                                            <?= $ann['is_pinned'] ? ' <i class="fas fa-thumbtack text-warning" title="Disematkan"></i>' : '' ?>
                                                            <?= !empty($ann['attachment_file']) ? ' <a href="../uploads/attachments/' . $ann['attachment_file'] . '" target="_blank"><i class="fas fa-paperclip" title="There is an Attachment"></i></a>' : '' ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            if ($ann['start_date'])
                                                                echo '<span class="badge bg-success">' . date('d/m/y', strtotime($ann['start_date'])) . '</span>';
                                                            if ($ann['end_date'])
                                                                echo ' - <span class="badge bg-danger">' . date('d/m/y', strtotime($ann['end_date'])) . '</span>';
                                                            if (!$ann['start_date'] && !$ann['end_date'])
                                                                echo '<span class="badge bg-secondary">Forever</span>';
                                                            ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($ann['username'] ?? 'N/A') ?></td>
                                                        <td>
                                                            <a href="manage_announcements.php?action=share&id=<?= $ann['id'] ?>"
                                                                class="btn btn-info btn-sm" title="Share Public Link"><i
                                                                    class="fas fa-share-alt"></i></a>
                                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                                data-bs-target="#editAnnouncementModal"
                                                                data-id="<?= $ann['id'] ?>">Edit</button>
                                                            <a href="manage_announcements.php?action=delete&id=<?= $ann['id'] ?>"
                                                                class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Certain?')">Delete</a>
                                                        </td>
                                                    </tr>
                                            <?php endforeach; else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">There has been no announcement yet.</td>
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

    <div class="modal fade" id="editAnnouncementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_announcements.php" method="POST" id="edit-form" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Announcement</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_announcement">
                        <input type="hidden" name="announcement_id" id="edit-announcement-id">
                        <input type="hidden" name="current_attachment" id="current-attachment">
                        <div class="mb-3"><label for="edit_title" class="form-label">Title</label><input type="text"
                                name="edit_title" id="edit_title" class="form-control" required></div>
                        <div class="mb-3"><label for="edit_content" class="form-label">Announcement
                                Contents</label><textarea name="edit_content" id="edit_content"
                                class="form-control"></textarea></div>
                        <div class="mb-3"><label for="edit_attachment" class="form-label">Change Attachment
                                (Optional)</label><input class="form-control" type="file" name="edit_attachment"
                                id="edit_attachment"></div>
                        <div id="current-attachment-info" class="mb-3" style="display: none;"><label
                                class="form-label">Current Attachments:</label>
                            <div><a href="#" id="current-attachment-link" target="_blank"></a></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_start_date" class="form-label">Start Date</label>
                                <input type="datetime-local" name="edit_start_date" id="edit_start_date"
                                    class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_end_date" class="form-label">End Date</label>
                                <input type="datetime-local" name="edit_end_date" id="edit_end_date"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="mb-3 form-check"><input type="checkbox" name="edit_is_pinned" id="edit_is_pinned"
                                class="form-check-input" value="1"><label for="edit_is_pinned"
                                class="form-check-label">Pin Announcement</label></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        const allAnnouncements = <?= json_encode(array_column($announcements, null, 'id')) ?>;
        let addEditor, editEditor;
        ClassicEditor.create(document.querySelector('#content')).then(e => addEditor = e).catch(err => console.error(err));
        ClassicEditor.create(document.querySelector('#edit_content')).then(e => { editEditor = e; e.ui.view.element.style.display = 'none'; }).catch(err => console.error(err));

        document.addEventListener('DOMContentLoaded', function () {
            const editModal = document.getElementById('editAnnouncementModal');
            editModal.addEventListener('show.bs.modal', function (event) {
                if (editEditor) editEditor.ui.view.element.style.display = 'block';
                const button = event.relatedTarget;
                const annId = button.getAttribute('data-id');
                const annData = allAnnouncements[annId];

                const modal = this;
                modal.querySelector('#edit-announcement-id').value = annId;
                modal.querySelector('#edit_title').value = annData.title;
                modal.querySelector('#edit_is_pinned').checked = (annData.is_pinned == 1);
                if (editEditor) editEditor.setData(annData.content || '');

                const currentAttachmentInfo = modal.querySelector('#current-attachment-info');
                const currentAttachmentLink = modal.querySelector('#current-attachment-link');
                modal.querySelector('#current-attachment').value = annData.attachment_file;

                if (annData.attachment_file) {
                    currentAttachmentLink.textContent = annData.attachment_file;
                    currentAttachmentLink.href = `../uploads/attachments/${annData.attachment_file}`;
                    currentAttachmentInfo.style.display = 'block';
                } else {
                    currentAttachmentInfo.style.display = 'none';
                }

                modal.querySelector('#edit_start_date').value = annData.start_date ? annData.start_date.slice(0, 16) : '';
                modal.querySelector('#edit_end_date').value = annData.end_date ? annData.end_date.slice(0, 16) : '';
            });

            editModal.addEventListener('hide.bs.modal', function () {
                if (editEditor) editEditor.ui.view.element.style.display = 'none';
            });
        });
    </script>
</body>

</html>