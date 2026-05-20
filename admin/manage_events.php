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
// bug fix hak akses, +manage_documents
$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('manage_events', $user_permissions) && $_SESSION['role'] !== 'Admin') {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
    header("Location: ../dashboard.php"); // Atau kembali ke dashboard jika login tapi tidak punya akses
    exit();
}
$admin_id = $_SESSION['user_id'];
$admin_username = $_SESSION['username'];

// Logika untuk Aksi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $action_success = false;
    $success_message = '';
    $error_message = '';

    if ($action === 'add_event') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $start_datetime = $_POST['start_datetime'];
        $end_datetime = !empty($_POST['end_datetime']) ? $_POST['end_datetime'] : null;
        $event_color = $_POST['event_color'];

        if (!empty($title) && !empty($start_datetime)) {
            $sql = "INSERT INTO events (title, description, start_datetime, end_datetime, event_color, created_by) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $title, $description, $start_datetime, $end_datetime, $event_color, $admin_id);
            if ($stmt->execute()) {
                $newEventId = $conn->insert_id;
                $action_success = true;
                $success_message = "<div class='alert alert-success'>Acara berhasil ditambahkan.</div>";
                logActivity($conn, $admin_id, 'create_event', "Admin '{$admin_username}' membuat acara: '" . htmlspecialchars($title) . "'");
                create_notification($conn, 'all', "Acara baru: '" . htmlspecialchars($title) . "' telah ditambahkan ke kalender.", "calendar.php#event-" . $newEventId, $admin_id);
            } else {
                $error_message = "<div class='alert alert-danger'>Gagal menambahkan acara.</div>";
            }
        }
    } elseif ($action === 'edit_event') {
        $id = (int) $_POST['event_id'];
        $title = trim($_POST['edit_title']);
        $description = trim($_POST['edit_description']);
        $start_datetime = $_POST['edit_start_datetime'];
        $end_datetime = !empty($_POST['edit_end_datetime']) ? $_POST['edit_end_datetime'] : null;
        $event_color = $_POST['edit_event_color'];

        if ($id > 0 && !empty($title) && !empty($start_datetime)) {
            $sql = "UPDATE events SET title = ?, description = ?, start_datetime = ?, end_datetime = ?, event_color = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $title, $description, $start_datetime, $end_datetime, $event_color, $id);
            if ($stmt->execute()) {
                $action_success = true;
                $success_message = "<div class='alert alert-success'>Acara berhasil diperbarui.</div>";
                logActivity($conn, $admin_id, 'edit_event', "Admin '{$admin_username}' mengedit acara: '" . htmlspecialchars($title) . "' (ID: {$id})");
            } else {
                $error_message = "<div class='alert alert-danger'>Gagal memperbarui acara.</div>";
            }
        }
    }

    if ($action_success) {
        $_SESSION['flash_message'] = $success_message;
    } else {
        $_SESSION['flash_message'] = $error_message ?: "<div class='alert alert-danger'>Terjadi kesalahan.</div>";
    }
    header("Location: manage_events.php");
    exit();
}

// Logika untuk Aksi (GET) - Hapus
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $sql = "DELETE FROM events WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "<div class='alert alert-success'>Acara berhasil dihapus.</div>";
        logActivity($conn, $admin_id, 'delete_event', "Admin '{$admin_username}' menghapus acara (ID: {$id})");
    } else {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal menghapus acara.</div>";
    }
    header("Location: manage_events.php");
    exit();
}

// Ambil semua data acara
$events = [];
$sql = "SELECT e.id, e.title, e.start_datetime, e.end_datetime, u.username 
        FROM events e 
        LEFT JOIN users u ON e.created_by = u.id 
        ORDER BY e.start_datetime DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

// Data untuk modal edit
$all_events_json = json_encode(array_column($events, null, 'id'));
?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Event Calendar Management</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="../assets/js/theme.js" defer></script>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <h2>Event Calendar Management</h2>
        <?= $message ?>
        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-header">Add New Event</div>
                    <div class="card-body">
                        <form action="manage_events.php" method="POST">
                            <input type="hidden" name="action" value="add_event">
                            <div class="mb-3">
                                <label for="title" class="form-label">Event Name</label>
                                <input type="text" name="title" id="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description (Optional)</label>
                                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_datetime" class="form-label">Start Date</label>
                                    <input type="datetime-local" name="start_datetime" id="start_datetime"
                                        class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="end_datetime" class="form-label">End Date (Optional)</label>
                                    <input type="datetime-local" name="end_datetime" id="end_datetime"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="event_color" class="form-label">Event Color</label>
                                <input type="color" name="event_color" id="event_color"
                                    class="form-control form-control-color" value="#3788d8" title="Pilih warna acara">
                            </div>
                            <button type="submit" class="btn btn-primary">Add Event</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 mb-4">
                <div class="card">
                    <div class="card-header">Event List</div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Date</th>
                                        <th>Created by</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($events) > 0):
                                        foreach ($events as $event): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($event['title']) ?></td>
                                                        <td><?= date('d M Y, H:i', strtotime($event['start_datetime'])) ?></td>
                                                        <td><?= htmlspecialchars($event['username'] ?? 'N/A') ?></td>
                                                        <td>
                                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                                data-bs-target="#editEventModal"
                                                                data-id="<?= $event['id'] ?>">Edit</button>
                                                            <a href="manage_events.php?action=delete&id=<?= $event['id'] ?>"
                                                                class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Anda yakin ingin menghapus acara ini?')">Hapus</a>
                                                        </td>
                                                    </tr>
                                            <?php endforeach; else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">Belum ada acara yang ditambahkan.</td>
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

    <div class="modal fade" id="editEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="manage_events.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_event">
                        <input type="hidden" name="event_id" id="edit-event-id">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Event Name</label>
                            <input type="text" name="edit_title" id="edit_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description (Optional)</label>
                            <textarea name="edit_description" id="edit_description" class="form-control"
                                rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_start_datetime" class="form-label">Start Date</label>
                                <input type="datetime-local" name="edit_start_datetime" id="edit_start_datetime"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_end_datetime" class="form-label">End Date (Optional)</label>
                                <input type="datetime-local" name="edit_end_datetime" id="edit_end_datetime"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_event_color" class="form-label">Event Color</label>
                            <input type="color" name="edit_event_color" id="edit_event_color"
                                class="form-control form-control-color" title="Pilih warna acara">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        const allEvents = <?= $all_events_json ?>;

        document.addEventListener('DOMContentLoaded', function () {
            const editModal = document.getElementById('editEventModal');
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const eventId = button.getAttribute('data-id');
                const eventData = allEvents[eventId];

                const modal = this;
                modal.querySelector('#edit-event-id').value = eventId;
                modal.querySelector('#edit_title').value = eventData.title;
                modal.querySelector('#edit_description').value = eventData.description || '';
                modal.querySelector('#edit_event_color').value = eventData.event_color || '#3788d8';

                // Format tanggal untuk input datetime-local
                modal.querySelector('#edit_start_datetime').value = eventData.start_datetime ? eventData.start_datetime.slice(0, 16) : '';
                modal.querySelector('#edit_end_datetime').value = eventData.end_datetime ? eventData.end_datetime.slice(0, 16) : '';
            });
        });
    </script>
</body>

</html>