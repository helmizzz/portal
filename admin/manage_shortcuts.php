<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Cek autentikasi dan hak akses admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Logika untuk Aksi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $action_success = false;
    $success_message = '';
    $error_message = '';

    if ($action === 'add_shortcut') {
        $name = trim($_POST['name']);
        $url = trim($_POST['url']);
        $icon_class = trim($_POST['icon_class']);
        $sort_order = (int) $_POST['sort_order'];

        if (!empty($name) && !empty($url) && !empty($icon_class)) {
            $sql = "INSERT INTO shortcuts (name, url, icon_class, sort_order) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $url, $icon_class, $sort_order);
            if ($stmt->execute()) {
                $action_success = true;
                $success_message = "<div class='alert alert-success'>Shortcut berhasil ditambahkan.</div>";
            } else {
                $error_message = "<div class='alert alert-danger'>Gagal menambahkan shortcut.</div>";
            }
        }
    } elseif ($action === 'edit_shortcut') {
        $id = (int) $_POST['id'];
        $name = trim($_POST['name']);
        $url = trim($_POST['url']);
        $icon_class = trim($_POST['icon_class']);
        $sort_order = (int) $_POST['sort_order'];

        if (!empty($name) && !empty($url) && !empty($icon_class) && $id > 0) {
            $sql = "UPDATE shortcuts SET name = ?, url = ?, icon_class = ?, sort_order = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssii", $name, $url, $icon_class, $sort_order, $id);
            if ($stmt->execute()) {
                $action_success = true;
                $success_message = "<div class='alert alert-success'>Shortcut berhasil diperbarui.</div>";
            } else {
                $error_message = "<div class='alert alert-danger'>Gagal memperbarui shortcut.</div>";
            }
        }
    }

    if ($action_success)
        $_SESSION['flash_message'] = $success_message;
    else
        $_SESSION['flash_message'] = $error_message ?: "<div class='alert alert-danger'>Terjadi kesalahan atau data tidak lengkap.</div>";

    header("Location: manage_shortcuts.php");
    exit();
}

// Logika untuk Aksi (GET) - Hapus
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $sql = "DELETE FROM shortcuts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "<div class='alert alert-success'>Shortcut berhasil dihapus.</div>";
    } else {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal menghapus shortcut.</div>";
    }
    header("Location: manage_shortcuts.php");
    exit();
}

// Ambil semua data shortcut
$shortcuts = [];
$result = $conn->query("SELECT * FROM shortcuts ORDER BY sort_order ASC");
while ($row = $result->fetch_assoc()) {
    $shortcuts[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Shortcut Management</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <h2>Shortcut Management</h2>
        <?= $message ?>

        <div class="card">
            <div class="card-header">Shortcut List</div>
            <div class="card-body">
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#shortcutModal"
                    data-action="add">
                    <i class="fas fa-plus"></i> Add New Shortcut
                </button>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Order</th>
                                <th>Icon</th>
                                <th>Name</th>
                                <th>Destination URL</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($shortcuts) > 0): ?>
                                    <?php foreach ($shortcuts as $item): ?>
                                            <tr>
                                                <td><?= $item['sort_order'] ?></td>
                                                <td><i class="<?= htmlspecialchars($item['icon_class']) ?> fa-2x"></i></td>
                                                <td><?= htmlspecialchars($item['name']) ?></td>
                                                <td class="text-truncate" style="max-width: 300px;">
                                                    <?= htmlspecialchars($item['url']) ?></td>
                                                <td>
                                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                        data-bs-target="#shortcutModal" data-id="<?= $item['id'] ?>"
                                                        data-name="<?= htmlspecialchars($item['name']) ?>"
                                                        data-url="<?= htmlspecialchars($item['url']) ?>"
                                                        data-icon="<?= htmlspecialchars($item['icon_class']) ?>"
                                                        data-order="<?= $item['sort_order'] ?>" data-action="edit">
                                                        Modify
                                                    </button>
                                                    <a href="manage_shortcuts.php?action=delete&id=<?= $item['id'] ?>"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Yakin ingin menghapus shortcut ini?');">
                                                        Delete
                                                    </a>
                                                </td>
                                            </tr>
                                    <?php endforeach; ?>
                            <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No shortcuts added yet.</td>
                                    </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shortcutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="manage_shortcuts.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shortcutModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" id="modal-action">
                        <input type="hidden" name="id" id="modal-id">

                        <div class="mb-3">
                            <label for="modal-name" class="form-label">Shortcut Name</label>
                            <input type="text" name="name" id="modal-name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="modal-url" class="form-label">Destination URL</label>
                            <input type="url" name="url" id="modal-url" class="form-control"
                                placeholder="https://contoh.com" required>
                        </div>
                        <div class="mb-3">
                            <label for="modal-icon" class="form-label">Font Awesome Icon Class</label>
                            <input type="text" name="icon_class" id="modal-icon" class="form-control"
                                placeholder="Contoh: fas fa-envelope" required>
                            <small class="form-text text-muted">Use class from <a
                                    href="https://fontawesome.com/v5/search" target="_blank">Font Awesome 5</a>.</small>
                        </div>
                        <div class="mb-3">
                            <label for="modal-order" class="form-label">Display Order</label>
                            <input type="number" name="sort_order" id="modal-order" class="form-control" value="0"
                                required>
                            <small class="form-text text-muted">Smaller numbers will appear first.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const shortcutModal = document.getElementById('shortcutModal');
            shortcutModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const action = button.getAttribute('data-action');

                const modalTitle = shortcutModal.querySelector('.modal-title');
                const form = shortcutModal.querySelector('form');
                const actionInput = form.querySelector('#modal-action');
                const idInput = form.querySelector('#modal-id');
                const nameInput = form.querySelector('#modal-name');
                const urlInput = form.querySelector('#modal-url');
                const iconInput = form.querySelector('#modal-icon');
                const orderInput = form.querySelector('#modal-order');

                if (action === 'edit') {
                    modalTitle.textContent = 'Edit Shortcut';
                    actionInput.value = 'edit_shortcut';
                    idInput.value = button.getAttribute('data-id');
                    nameInput.value = button.getAttribute('data-name');
                    urlInput.value = button.getAttribute('data-url');
                    iconInput.value = button.getAttribute('data-icon');
                    orderInput.value = button.getAttribute('data-order');
                } else {
                    modalTitle.textContent = 'Tambah Shortcut Baru';
                    actionInput.value = 'add_shortcut';
                    form.reset();
                    idInput.value = '';
                    orderInput.value = 0;
                }
            });
        });
    </script>
</body>

</html>