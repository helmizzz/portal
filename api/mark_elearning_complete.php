<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once '../includes/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$material_id = isset($data['material_id']) ? (int)$data['material_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($material_id === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid material ID']);
    exit();
}

// Cek agar tidak ada duplikat
$sql_check = "SELECT id FROM elearning_completion WHERE user_id = ? AND material_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $material_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Already marked as complete.']);
    exit();
}

$sql_insert = "INSERT INTO elearning_completion (user_id, material_id) VALUES (?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("ii", $user_id, $material_id);

if ($stmt_insert->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save completion status.']);
}

$stmt_check->close();
$stmt_insert->close();
$conn->close();
?>