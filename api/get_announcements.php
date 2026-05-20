<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../includes/db_connect.php';

date_default_timezone_set('Asia/Jakarta');
$current_time = date('Y-m-d H:i:s');
$user_id = $_SESSION['user_id'];

$feed = [];

// 1. Ambil data PENGUMUMAN yang aktif
$sql_ann = "SELECT id, title, created_at, is_pinned, 'announcement' as type, NULL as link FROM announcements WHERE (start_date IS NULL OR start_date <= ?) AND (end_date IS NULL OR end_date >= ?) ORDER BY is_pinned DESC, created_at DESC";
$stmt_ann = $conn->prepare($sql_ann);
$stmt_ann->bind_param("ss", $current_time, $current_time);
$stmt_ann->execute();
$result_ann = $stmt_ann->get_result();
while ($row = $result_ann->fetch_assoc()) {
    $feed[] = $row;
}

// 2. Ambil data NOTIFIKASI (misal, 10 notifikasi terbaru)
$sql_notif = "SELECT id, message as title, created_at, 'notification' as type, 0 as is_pinned, link FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param("i", $user_id);
$stmt_notif->execute();
$result_notif = $stmt_notif->get_result();
while ($row = $result_notif->fetch_assoc()) {
    $feed[] = $row;
}

// 3. Ambil data ACARA/EVENT (misal, 10 acara terbaru)
$sql_events = "SELECT id, title, created_at, 'event' as type, 0 as is_pinned, 'calendar.php' as link FROM events ORDER BY created_at DESC LIMIT 10";
$result_events = $conn->query($sql_events);
while ($row = $result_events->fetch_assoc()) {
    $feed[] = $row;
}

// 4. Ambil data POLLING/SURVEI (misal, 10 survei terbaru yang aktif)
$sql_polls = "SELECT id, title, created_at, 'poll' as type, 0 as is_pinned, 'polls.php' as link FROM polls WHERE status = 'active' ORDER BY created_at DESC LIMIT 10";
$result_polls = $conn->query($sql_polls);
while ($row = $result_polls->fetch_assoc()) {
    $feed[] = $row;
}


// 5. Urutkan data gabungan berdasarkan tanggal, dengan yang disematkan (pinned) selalu di atas
usort($feed, function($a, $b) {
    if ($a['is_pinned'] != $b['is_pinned']) {
        return $b['is_pinned'] <=> $a['is_pinned'];
    }
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});


echo json_encode(array_slice($feed, 0, 20)); // Kirim 20 item teratas dari gabungan

$conn->close();
?>