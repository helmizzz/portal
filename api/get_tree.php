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
$user_role = $_SESSION['role'] ?? '';
$user_departement_id = null;

// Get user's department
$sql_user_dept = "SELECT departement_id FROM users WHERE id = ?";
$stmt_user_dept = $conn->prepare($sql_user_dept);
$stmt_user_dept->bind_param("i", $user_id);
$stmt_user_dept->execute();
$result_user_dept = $stmt_user_dept->get_result();
if ($result_user_dept->num_rows > 0) {
    $user_data = $result_user_dept->fetch_assoc();
    $user_departement_id = $user_data['departement_id'];
}
$stmt_user_dept->close();

$user_permissions = getUserPermissions($conn, $user_id);
$can_view_all = in_array('manage_documentsrevsop', $user_permissions) || $user_role === 'Admin';

$search_query = $_GET['q'] ?? '';
$date_filter = $_GET['date'] ?? '';
$dept_filter_id = $_GET['dept'] ?? '';
$year_filter_id = $_GET['year'] ?? '';

// Build Hierarchy: Dept -> Year -> Documents

function getTreeData($conn, $user_id, $user_departement_id, $can_view_all, $search_query, $date_filter, $dept_filter_id, $year_filter_id) {
    $tree = [];

    // 1. Fetch Departments
    $sql_depts = "SELECT id, name FROM departements";
    if (!$can_view_all && $user_departement_id) {
        $sql_depts .= " WHERE id = " . intval($user_departement_id);
    } elseif (!empty($dept_filter_id)) {
        $sql_depts .= " WHERE id = " . intval($dept_filter_id);
    }
    $sql_depts .= " ORDER BY name ASC";
    $res_depts = $conn->query($sql_depts);

    while ($dept = $res_depts->fetch_assoc()) {
        $dept_node = [
            'id' => 'dept-' . $dept['id'],
            'type' => 'folder',
            'name' => $dept['name'],
            'children' => []
        ];

        // 2. Fetch Years for this department
        $sql_years = "SELECT DISTINCT t.id_tahun, t.tahun 
                      FROM tahun t 
                      JOIN documents d ON d.tahun = t.tahun 
                      WHERE d.nama_dept = '" . $conn->real_escape_string($dept['name']) . "'";
        
        if (!empty($year_filter_id)) {
            $sql_years .= " AND t.id_tahun = " . intval($year_filter_id);
        }
        $sql_years .= " ORDER BY t.tahun DESC";
        $res_years = $conn->query($sql_years);

        while ($year = $res_years->fetch_assoc()) {
            $year_node = [
                'id' => 'year-' . $dept['id'] . '-' . $year['id_tahun'],
                'type' => 'folder',
                'name' => $year['tahun'],
                'children' => []
            ];

            // 3. Fetch Documents for this dept and year
            $sql_docs = "SELECT id, file_name, file_code, uploaded_at, updated_at, created_by 
                         FROM documents 
                         WHERE nama_dept = '" . $conn->real_escape_string($dept['name']) . "' 
                         AND tahun = '" . $conn->real_escape_string($year['tahun']) . "'";
            
            if (!empty($search_query)) {
                $sql_docs .= " AND (file_name LIKE '%" . $conn->real_escape_string($search_query) . "%' OR file_code LIKE '%" . $conn->real_escape_string($search_query) . "%')";
            }
            if (!empty($date_filter)) {
                $sql_docs .= " AND DATE(uploaded_at) = '" . $conn->real_escape_string($date_filter) . "'";
            }
            $sql_docs .= " ORDER BY file_name ASC";
            $res_docs = $conn->query($sql_docs);

            while ($doc = $res_docs->fetch_assoc()) {
                $year_node['children'][] = [
                    'id' => 'doc-' . $doc['id'],
                    'type' => 'document',
                    'name' => $doc['file_name'],
                    // Keep basename here if user wants to construct path in JS
                    'file_name' => $doc['file_name'],
                    'tahun' => $year['tahun'],
                    'nama_dept' => $dept['name'],
                    'doc_id' => $doc['id'],
                    'upload_at' => $doc['uploaded_at'],
                    'update_at' => $doc['updated_at'] ?: '-',
                    'upload_by' => $doc['created_by'],
                    'kode_file' => $doc['file_code']
                ];
            }

            // Only add year node if it has children or no search query is active
            if (!empty($year_node['children']) || (empty($search_query) && empty($date_filter))) {
                $dept_node['children'][] = $year_node;
            }
        }
        // Menampilkan dept yang tidak memiliki anak (tahun/document)
        // if (!empty($dept_node['children']) || (empty($search_query) && empty($date_filter) && empty($year_filter_id))) {
        // Menampilkan dept yang memiliki anak (tahun/document)
        if (!empty($dept_node['children'])) {
            $tree[] = $dept_node;
        }
    }

    return $tree;
}

$treeData = getTreeData($conn, $user_id, $user_departement_id, $can_view_all, $search_query, $date_filter, $dept_filter_id, $year_filter_id);
echo json_encode($treeData);

$conn->close();
?>