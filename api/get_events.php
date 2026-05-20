<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../includes/db_connect.php';

$events = [];
$sql = "SELECT id, title, description, start_datetime, end_datetime, event_color FROM events";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id'          => $row['id'],
        'title'       => $row['title'],
        'start'       => $row['start_datetime'],
        'end'         => $row['end_datetime'],
        'color'       => $row['event_color'],
        'description' => $row['description']
    ];
}

echo json_encode($events);

$conn->close();
?>