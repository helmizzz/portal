<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Cek hak akses
$permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('view_analytics', $permissions)) {
    header("Location: ../dashboard.php");
    exit();
}

// Ambil daftar dokumen dan pengumuman untuk dropdown
$documents = $conn->query("SELECT id, file_name FROM documents ORDER BY file_name ASC")->fetch_all(MYSQLI_ASSOC);
$announcements = $conn->query("SELECT id, title FROM announcements ORDER BY title DESC")->fetch_all(MYSQLI_ASSOC);

$selected_item = $_GET['item'] ?? '';
$readers = [];
$non_readers = [];
$item_title = '';

if (!empty($selected_item)) {
    list($type, $id) = explode('-', $selected_item);
    $id = (int) $id;

    $all_active_users_result = $conn->query("SELECT id, username FROM users WHERE is_active = 1");
    $all_users = [];
    while ($user = $all_active_users_result->fetch_assoc()) {
        $all_users[$user['id']] = $user['username'];
    }

    $reader_ids = [];

    if ($type === 'doc' && $id > 0) {
        $item_title = $conn->query("SELECT file_name FROM documents WHERE id = $id")->fetch_assoc()['file_name'];
        $sql_readers = "SELECT DISTINCT al.user_id, u.username, al.timestamp 
                        FROM activity_logs al
                        JOIN users u ON al.user_id = u.id
                        WHERE al.activity_type = 'view_document' AND al.description LIKE ?
                        ORDER BY al.timestamp DESC";
        $stmt_readers = $conn->prepare($sql_readers);
        $like_param = "%melihat dokumen '" . $conn->real_escape_string($item_title) . "'%";
        $stmt_readers->bind_param("s", $like_param);
        $stmt_readers->execute();
        $result_readers = $stmt_readers->get_result();
        while ($row = $result_readers->fetch_assoc()) {
            if (!in_array($row['user_id'], $reader_ids)) {
                $readers[] = $row;
                $reader_ids[] = $row['user_id'];
            }
        }

    } elseif ($type === 'ann' && $id > 0) {
        $item_title = $conn->query("SELECT title FROM announcements WHERE id = $id")->fetch_assoc()['title'];
        $sql_readers = "SELECT DISTINCT al.user_id, u.username, al.timestamp
                        FROM activity_logs al
                        JOIN users u ON al.user_id = u.id
                        WHERE al.activity_type = 'view_announcement' AND al.description LIKE ?
                        ORDER BY al.timestamp DESC";
        $stmt_readers = $conn->prepare($sql_readers);
        $like_param = "%melihat pengumuman: '" . $conn->real_escape_string($item_title) . "'%";
        $stmt_readers->bind_param("s", $like_param);
        $stmt_readers->execute();
        $result_readers = $stmt_readers->get_result();
        while ($row = $result_readers->fetch_assoc()) {
            if (!in_array($row['user_id'], $reader_ids)) {
                $readers[] = $row;
                $reader_ids[] = $row['user_id'];
            }
        }
    }

    foreach ($all_users as $user_id => $username) {
        if (!in_array($user_id, $reader_ids)) {
            $non_readers[] = ['user_id' => $user_id, 'username' => $username];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Readership Report</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="../assets/js/theme.js" defer></script>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <h2><i class="fas fa-eye me-2"></i>Readership Report</h2>
        <p class="text-muted">Select an item to view a specific report, or export directly to get the overall report.
        </p>

        <div class="card mb-4">
            <div class="card-header">Select Item</div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="item-select" class="form-label">Document or Announcement</label>
                        <select name="item" id="item-select" class="form-select">
                            <option value="">-- Select an Item (for viewing on this page) --</option>
                            <optgroup label="Documents">
                                <?php foreach ($documents as $doc): ?>
                                        <option value="doc-<?= $doc['id'] ?>" <?= ($selected_item == 'doc-' . $doc['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($doc['file_name']) ?>
                                        </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Announcements">
                                <?php foreach ($announcements as $ann): ?>
                                        <option value="ann-<?= $ann['id'] ?>" <?= ($selected_item == 'ann-' . $ann['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ann['title']) ?>
                                        </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex">
                        <button type="submit" class="btn btn-primary flex-grow-1 me-2">Generate Report</button>
                        <a href="export_readership.php?item=all" id="export-btn" class="btn btn-success flex-grow-1"
                            title="Ekspor ke Excel">
                            <i class="fas fa-file-excel"></i> <span id="export-btn-text">Export All</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($selected_item)): ?>
                <h3 class="mt-4 mb-3">Report for: <span class="text-primary"><?= htmlspecialchars($item_title) ?></span></h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-check-circle me-2"></i> Has Read (<?= count($readers) ?> Users)
                            </div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Time Read</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($readers) > 0):
                                            foreach ($readers as $reader): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($reader['username']) ?></td>
                                                            <td><?= date('d M Y, H:i', strtotime($reader['timestamp'])) ?></td>
                                                        </tr>
                                                <?php endforeach; else: ?>
                                                <tr>
                                                    <td colspan="2" class="text-center">No one has read this item yet.</td>
                                                </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <i class="fas fa-times-circle me-2"></i> Has Not Read (<?= count($non_readers) ?> Users)
                            </div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($non_readers) > 0):
                                            foreach ($non_readers as $non_reader): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($non_reader['username']) ?></td>
                                                        </tr>
                                                <?php endforeach; else: ?>
                                                <tr>
                                                    <td class="text-center">All active users have read this item.</td>
                                                </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        <?php endif; ?>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const exportBtn = document.getElementById('export-btn');
            const exportBtnText = document.getElementById('export-btn-text');
            const itemSelect = document.getElementById('item-select');

            function updateExportLink() {
                const selectedValue = itemSelect.value;
                if (selectedValue) {
                    exportBtn.href = `export_readership.php?item=${selectedValue}`;
                    exportBtnText.textContent = 'Export Selected';
                } else {
                    exportBtn.href = 'export_readership.php?item=all';
                    exportBtnText.textContent = 'Export All';
                }
            }

            updateExportLink();
            itemSelect.addEventListener('change', updateExportLink);
        });
    </script>
</body>

</html>