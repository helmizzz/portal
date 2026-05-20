<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

session_start();

// Cek hak akses
if (!isset($_SESSION['user_id']) || !in_array('view_analytics', getUserPermissions($conn, $_SESSION['user_id']))) {
    die("Akses ditolak.");
}

function getReadershipData($conn, $item_title, $type) {
    $all_active_users_result = $conn->query("SELECT id, username FROM users WHERE is_active = 1");
    $all_users = [];
    while($user = $all_active_users_result->fetch_assoc()) {
        $all_users[$user['id']] = $user['username'];
    }
    
    $readers = [];
    $reader_ids = [];

    if ($type === 'doc') {
        $sql_readers = "SELECT DISTINCT al.user_id, u.username, al.timestamp FROM activity_logs al JOIN users u ON al.user_id = u.id WHERE al.activity_type = 'view_document' AND al.description LIKE ? ORDER BY al.timestamp DESC";
        $like_param = "%melihat dokumen '" . $conn->real_escape_string($item_title) . "'%";
    } else { // ann
        $sql_readers = "SELECT DISTINCT al.user_id, u.username, al.timestamp FROM activity_logs al JOIN users u ON al.user_id = u.id WHERE al.activity_type = 'view_announcement' AND al.description LIKE ? ORDER BY al.timestamp DESC";
        $like_param = "%melihat pengumuman: '" . $conn->real_escape_string($item_title) . "'%";
    }
    
    $stmt_readers = $conn->prepare($sql_readers);
    $stmt_readers->bind_param("s", $like_param);
    $stmt_readers->execute();
    $result_readers = $stmt_readers->get_result();
    while($row = $result_readers->fetch_assoc()) {
        if (!in_array($row['user_id'], $reader_ids)) {
             $readers[] = $row;
             $reader_ids[] = $row['user_id'];
        }
    }

    $non_readers = [];
    foreach ($all_users as $user_id => $username) {
        if (!in_array($user_id, $reader_ids)) {
            $non_readers[] = ['username' => $username];
        }
    }
    
    return ['readers' => $readers, 'non_readers' => $non_readers];
}

$selected_item = $_GET['item'] ?? '';
if (empty($selected_item)) {
    die("Tidak ada item yang dipilih untuk diekspor.");
}

// Persiapan File CSV
$filename = "readership_report_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');

// KASUS 1: EKSPOR SEMUA ITEM
if ($selected_item === 'all') {
    $filename = "readership_report_all_items_" . date('Y-m-d') . ".csv";
    // Set header lagi untuk menimpa nama file
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Ambil semua dokumen
    $documents = $conn->query("SELECT id, title FROM documents ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);
    // Ambil semua pengumuman
    $announcements = $conn->query("SELECT id, title FROM announcements ORDER BY title DESC")->fetch_all(MYSQLI_ASSOC);

    // Proses Dokumen
    fputcsv($output, ['--- LAPORAN KETERBACAAN DOKUMEN ---']);
    fputcsv($output, []);
    foreach ($documents as $doc) {
        fputcsv($output, ['Report for Document:', $doc['title']]);
        $data = getReadershipData($conn, $doc['title'], 'doc');
        fputcsv($output, ['Status', 'Username', 'Time Read']);
        foreach ($data['readers'] as $reader) {
            fputcsv($output, ['Has Read', $reader['username'], date('d-m-Y H:i:s', strtotime($reader['timestamp']))]);
        }
        foreach ($data['non_readers'] as $non_reader) {
            fputcsv($output, ['Has Not Read', $non_reader['username'], '']);
        }
        fputcsv($output, []); // Separator
    }

    // Proses Pengumuman
    fputcsv($output, ['--- LAPORAN KETERBACAAN PENGUMUMAN ---']);
    fputcsv($output, []);
     foreach ($announcements as $ann) {
        fputcsv($output, ['Report for Announcement:', $ann['title']]);
        $data = getReadershipData($conn, $ann['title'], 'ann');
        fputcsv($output, ['Status', 'Username', 'Time Read']);
        foreach ($data['readers'] as $reader) {
            fputcsv($output, ['Has Read', $reader['username'], date('d-m-Y H:i:s', strtotime($reader['timestamp']))]);
        }
        foreach ($data['non_readers'] as $non_reader) {
            fputcsv($output, ['Has Not Read', $non_reader['username'], '']);
        }
        fputcsv($output, []); // Separator
    }

// KASUS 2: EKSPOR SATU ITEM SPESIFIK
} else {
    // BARIS BERMASALAH DIPINDAHKAN KE DALAM BLOK INI
    list($type, $id) = explode('-', $selected_item);
    $id = (int)$id;

    if ($type === 'doc') {
        $item_title = $conn->query("SELECT title FROM documents WHERE id = $id")->fetch_assoc()['title'];
    } else {
        $item_title = $conn->query("SELECT title FROM announcements WHERE id = $id")->fetch_assoc()['title'];
    }

    $filename = "readership_report_" . preg_replace('/[^a-z0-9]+/', '_', strtolower($item_title)) . "_" . date('Y-m-d') . ".csv";
    // Set header lagi untuk menimpa nama file
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $data = getReadershipData($conn, $item_title, $type);
    
    fputcsv($output, ['Readership Report for:', $item_title]);
    fputcsv($output, []);
    fputcsv($output, ['Status', 'Username', 'Time Read']);
    if (count($data['readers']) > 0) {
        foreach ($data['readers'] as $reader) {
            fputcsv($output, ['Has Read', $reader['username'], date('d-m-Y H:i:s', strtotime($reader['timestamp']))]);
        }
    }
    if (count($data['non_readers']) > 0) {
        foreach ($data['non_readers'] as $non_reader) {
            fputcsv($output, ['Has Not Read', $non_reader['username'], '']);
        }
    }
}

fclose($output);
exit();