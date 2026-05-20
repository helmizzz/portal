<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Cek hak akses
$permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('view_audit_trail', $permissions)) {
    header("Location: ../dashboard.php");
    exit();
}

// Filter
$where_clauses = [];
$params = [];
$types = '';

if (!empty($_GET['user_id'])) {
    $where_clauses[] = "al.user_id = ?";
    $params[] = (int) $_GET['user_id'];
    $types .= 'i';
}
if (!empty($_GET['activity_type'])) {
    $where_clauses[] = "al.activity_type = ?";
    $params[] = $_GET['activity_type'];
    $types .= 's';
}
if (!empty($_GET['start_date'])) {
    $where_clauses[] = "al.timestamp >= ?";
    $params[] = $_GET['start_date'] . ' 00:00:00';
    $types .= 's';
}
if (!empty($_GET['end_date'])) {
    $where_clauses[] = "al.timestamp <= ?";
    $params[] = $_GET['end_date'] . ' 23:59:59';
    $types .= 's';
}

$where_sql = count($where_clauses) > 0 ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$sql = "SELECT al.id, u.username, al.activity_type, al.description, al.timestamp 
        FROM activity_logs al 
        JOIN users u ON al.user_id = u.id 
        $where_sql 
        ORDER BY al.timestamp DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Data untuk filter dropdown
$users = $conn->query("SELECT id, username FROM users ORDER BY username")->fetch_all(MYSQLI_ASSOC);
$activity_types = $conn->query("SELECT DISTINCT activity_type FROM activity_logs ORDER BY activity_type")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>

    <head>
        <meta charset="UTF-8">
        <title>Audit Trail</title>
        <link rel="icon" type="image/png" href="../uploads/EIP.png">
        <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link rel="stylesheet" href="../assets/css/style.css">
        <script src="../assets/js/theme.js" defer></script>

    <body class="<?= $theme_class ?>"> <?php include 'admin_nav.php'; ?>
        <div class="container mt-4">
            <h2>Audit Trail</h2>

            <div class="card mb-4">
                <div class="card-header">Activity Log Filter</div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <select name="user_id" class="form-select">
                                <option value="">All Users</option>
                                <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>" <?= ($_GET['user_id'] ?? '') == $user['id'] ? 'selected' : '' ?>><?= htmlspecialchars($user['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="activity_type" class="form-select">
                                <option value="">All Types of Activities</option>
                                <?php foreach ($activity_types as $type): ?>
                                        <option value="<?= $type['activity_type'] ?>" <?= ($_GET['activity_type'] ?? '') == $type['activity_type'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($type['activity_type']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2"><input type="date" name="start_date" class="form-control"
                                value="<?= $_GET['start_date'] ?? '' ?>"></div>
                        <div class="col-md-2"><input type="date" name="end_date" class="form-control"
                                value="<?= $_GET['end_date'] ?? '' ?>"></div>
                        <div class="col-md-2 d-grid"><button type="submit" class="btn btn-primary">Filter</button></div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    Hasil Log
                    <a href="export_audit_log.php?<?= http_build_query($_GET) ?>"
                        class="btn btn-success btn-sm float-end">Export to CSV</a>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Time</th>
                                <th>Users</th>
                                <th>Activity Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($logs) > 0): ?>
                                    <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?= $log['id'] ?></td>
                                                <td><?= date('d-m-Y H:i:s', strtotime($log['timestamp'])) ?></td>
                                                <td><?= htmlspecialchars($log['username']) ?></td>
                                                <td><span
                                                        class="badge bg-secondary"><?= htmlspecialchars($log['activity_type']) ?></span>
                                                </td>
                                                <td><?= htmlspecialchars($log['description']) ?></td>
                                            </tr>
                                    <?php endforeach; ?>
                            <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No logs matched the filter.</td>
                                    </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>

</html>