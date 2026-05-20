<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../includes/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$theme = $data['theme'] ?? 'light';

if ($theme !== 'light' && $theme !== 'dark') {
    $theme = 'light';
}

$user_id = $_SESSION['user_id'];

$sql = "UPDATE users SET theme = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $theme, $user_id);

if ($stmt->execute()) {
    $_SESSION['theme'] = $theme;
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save theme preference.']);
}

$stmt->close();
$conn->close();
?>