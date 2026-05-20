<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../includes/db_connect.php';

// **PERUBAHAN DI SINI:**
// Kueri diubah untuk hanya mengambil pengguna yang memiliki departement_id
$sql = "SELECT 
            u.id as user_id, 
            u.username, 
            u.profile_picture,
            d.id as department_id,
            d.name as department_name,
            r.role_name
        FROM users u
        INNER JOIN departements d ON u.departement_id = d.id
        LEFT JOIN roles r ON u.role_id = r.id
        WHERE u.is_active = 1 AND u.departement_id IS NOT NULL
        ORDER BY d.name, u.username";

$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
    exit();
}

$departments = [];
$users = [];

while ($row = $result->fetch_assoc()) {
    $dept_id = $row['department_id'];
    $dept_name = $row['department_name'];

    if (!isset($departments[$dept_id])) {
        $departments[$dept_id] = $dept_name;
    }
    $users[] = $row;
}

// Bangun struktur data untuk library org-chart
$data = [];

// 1. Tambahkan node root (perusahaan)
$data[] = [
    'id' => 'root',
    'parentId' => '',
    'name' => 'Perusahaan',
    'title' => 'Struktur Organisasi'
];

// 2. Tambahkan node departemen
foreach ($departments as $id => $name) {
    $data[] = [
        'id' => 'dept-' . $id,
        'parentId' => 'root',
        'name' => $name,
        'title' => 'Departemen'
    ];
}

// 3. Tambahkan node karyawan
foreach ($users as $user) {
    $profile_pic_path = 'uploads/profiles/default-profile.png';
    if (!empty($user['profile_picture']) && file_exists('../uploads/profiles/' . $user['profile_picture'])) {
        $profile_pic_path = 'uploads/profiles/' . $user['profile_picture'];
    }

    $parent_dept_id = $user['department_id'];
    $role_name = $user['role_name'] ?? 'No Position';

    $data[] = [
        'id' => 'user-' . $user['user_id'],
        'parentId' => 'dept-' . $parent_dept_id,
        'name' => $user['username'],
        'title' => $role_name,
        'imageUrl' => $profile_pic_path
    ];
}

echo json_encode($data);

$conn->close();