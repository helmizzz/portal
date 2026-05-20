<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Cek hak akses
$permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('view_audit_trail', $permissions)) {
    exit('Akses ditolak.');
}

// Ambil filter dari query string
$where_clauses = [];
$params = [];
$types = '';
if (!empty($_GET['user_id'])) {
    $where_clauses[] = "al.user_id = ?";
    $params[] = (int)$_GET['user_id'];
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
$result = $stmt->get_result();

// Header untuk file CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="audit_log_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Timestamp', 'Username', 'Activity Type', 'Description']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit();
?>