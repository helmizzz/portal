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
$document_id = isset($data['document_id']) ? (int)$data['document_id'] : 0;
$user_id = $_SESSION['user_id'];

if ($document_id === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid document ID.']);
    exit();
}

// Cek apakah sudah menjadi favorit
$sql_check = "SELECT * FROM user_favorite_documents WHERE user_id = ? AND document_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $user_id, $document_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Jika sudah ada, hapus dari favorit
    $sql_delete = "DELETE FROM user_favorite_documents WHERE user_id = ? AND document_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $user_id, $document_id);
    if ($stmt_delete->execute()) {
        echo json_encode(['success' => true, 'status' => 'removed']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to remove favorite.']);
    }
    $stmt_delete->close();
} else {
    // Jika belum ada, tambahkan ke favorit
    $sql_insert = "INSERT INTO user_favorite_documents (user_id, document_id) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ii", $user_id, $document_id);
    if ($stmt_insert->execute()) {
        echo json_encode(['success' => true, 'status' => 'added']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add favorite.']);
    }
    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();
?>