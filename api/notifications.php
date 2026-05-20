<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../includes/db_connect.php';

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'fetch') {
    $sql = "SELECT id, message, link, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    $sql_unread_count = "SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0";
    $stmt_unread_count = $conn->prepare($sql_unread_count);
    $stmt_unread_count->bind_param("i", $user_id);
    $stmt_unread_count->execute();
    $unread_count = $stmt_unread_count->get_result()->fetch_assoc()['unread_count'];

    echo json_encode(['notifications' => $notifications, 'unread_count' => $unread_count]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'mark_read') {
    $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}

$conn->close();
?>