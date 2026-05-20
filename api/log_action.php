<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$data = json_decode(file_get_contents('php://input'), true);
$action_type = $data['action_type'] ?? null;
$document_name = $data['document_name'] ?? 'N/A';

if ($action_type) {
    $description = '';
    switch ($action_type) {
        case 'attempt_right_click':
            $description = "User '{$username}' mencoba melakukan klik kanan saat melihat dokumen '{$document_name}'.";
            break;
        case 'attempt_print_screen':
            $description = "User '{$username}' mencoba melakukan screenshot saat melihat dokumen '{$document_name}'.";
            break;
        case 'attempt_print':
            $description = "User '{$username}' mencoba melakukan print (Ctrl+P) saat melihat dokumen '{$document_name}'.";
            break;
        case 'attempt_save':
            $description = "User '{$username}' mencoba menyimpan halaman (Ctrl+S) saat melihat dokumen '{$document_name}'.";
            break;
        case 'view_document':
            $description = "User '{$username}' melihat dokumen '{$document_name}'.";
            break;
    }

    if (!empty($description)) {
        logActivity($conn, $user_id, $action_type, $description);
        echo json_encode(['success' => true, 'message' => 'Activity logged.']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action type']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No action type provided']);
}
?>