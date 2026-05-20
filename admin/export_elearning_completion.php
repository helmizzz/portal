<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

session_start();

// Cek hak akses
if (!isset($_SESSION['user_id']) || !in_array('view_elearning_report', getUserPermissions($conn, $_SESSION['user_id']))) {
    die("Akses ditolak.");
}

/**
 * Mengambil data penyelesaian untuk sebuah kursus.
 * @return array ['completers' => [], 'non_completers' => []]
 */
function getCourseCompletionData($conn, $course_id) {
    $completers = [];
    $non_completers = [];

    // 1. Dapatkan jumlah total materi dalam kursus
    $stmt_total = $conn->prepare("SELECT COUNT(id) as total FROM elearning_materials WHERE course_id = ?");
    $stmt_total->bind_param("i", $course_id);
    $stmt_total->execute();
    $total_materials = $stmt_total->get_result()->fetch_assoc()['total'];

    if ($total_materials == 0) {
        return ['completers' => [], 'non_completers' => []];
    }

    // 2. Dapatkan semua pengguna aktif
    $all_users_result = $conn->query("SELECT id, username FROM users WHERE is_active = 1");
    $all_users = [];
    while ($user = $all_users_result->fetch_assoc()) {
        $all_users[$user['id']] = $user['username'];
    }

    // 3. Dapatkan pengguna yang telah menyelesaikan semua materi
    $sql_completers = "SELECT ec.user_id
                       FROM elearning_completion ec
                       JOIN elearning_materials em ON ec.material_id = em.id
                       WHERE em.course_id = ?
                       GROUP BY ec.user_id
                       HAVING COUNT(DISTINCT ec.material_id) = ?";
    $stmt_completers = $conn->prepare($sql_completers);
    $stmt_completers->bind_param("ii", $course_id, $total_materials);
    $stmt_completers->execute();
    $result_completers = $stmt_completers->get_result();

    $completer_ids = [];
    while ($row = $result_completers->fetch_assoc()) {
        $user_id = $row['user_id'];
        if (isset($all_users[$user_id])) {
            $completers[] = ['username' => $all_users[$user_id]];
            $completer_ids[] = $user_id;
        }
    }

    // 4. Tentukan pengguna yang belum selesai
    foreach ($all_users as $user_id => $username) {
        if (!in_array($user_id, $completer_ids)) {
            $non_completers[] = ['username' => $username];
        }
    }

    return ['completers' => $completers, 'non_completers' => $non_completers];
}

$selected_item = $_GET['item'] ?? '';
if (empty($selected_item)) {
    die("Tidak ada item yang dipilih untuk diekspor.");
}

// Persiapan File CSV
$filename = "elearning_completion_report_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');

// KASUS 1: EKSPOR SEMUA KURSUS
if ($selected_item === 'all') {
    $filename = "elearning_report_all_courses_" . date('Y-m-d') . ".csv";
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $courses = $conn->query("SELECT id, title FROM elearning_courses ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);

    fputcsv($output, ['--- LAPORAN PENYELESAIAN E-LEARNING ---']);
    fputcsv($output, []);
    foreach ($courses as $course) {
        fputcsv($output, ['Laporan untuk Kursus:', $course['title']]);
        $data = getCourseCompletionData($conn, $course['id']);
        fputcsv($output, ['Status', 'Username']);
        foreach ($data['completers'] as $user) {
            fputcsv($output, ['Telah Menyelesaikan', $user['username']]);
        }
        foreach ($data['non_completers'] as $user) {
            fputcsv($output, ['Belum Menyelesaikan', $user['username']]);
        }
        fputcsv($output, []); // Baris kosong sebagai pemisah
    }

// KASUS 2: EKSPOR SATU KURSUS SPESIFIK
} else {
    list($type, $id) = explode('-', $selected_item);
    $id = (int)$id;

    if ($type === 'course') {
        $item_title = $conn->query("SELECT title FROM elearning_courses WHERE id = $id")->fetch_assoc()['title'];
        $filename = "elearning_report_" . preg_replace('/[^a-z0-9]+/', '_', strtolower($item_title)) . "_" . date('Y-m-d') . ".csv";
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $data = getCourseCompletionData($conn, $id);
        
        fputcsv($output, ['Laporan Penyelesaian untuk:', $item_title]);
        fputcsv($output, []);
        fputcsv($output, ['Status', 'Username']);
        foreach ($data['completers'] as $user) {
            fputcsv($output, ['Telah Menyelesaikan', $user['username']]);
        }
        foreach ($data['non_completers'] as $user) {
            fputcsv($output, ['Belum Menyelesaikan', $user['username']]);
        }
    }
}

fclose($output);
exit();