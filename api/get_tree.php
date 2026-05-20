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
$can_view_all = in_array('view_all_documents', $user_permissions)
    || in_array('manage_documentsrev', $user_permissions)
    || in_array('manage_documentsrevsop', $user_permissions)
    || $user_role === 'Admin';

$search_query = $_GET['q'] ?? '';
$date_filter = $_GET['date'] ?? '';
$dept_filter_id = $_GET['dept'] ?? '';
$year_filter_id = $_GET['year'] ?? '';

// Build Hierarchy: Public -> Year -> Documents, then Dept -> Year -> Private Documents

function getTreeData($conn, $user_id, $user_departement_id, $can_view_all, $search_query, $date_filter, $dept_filter_id, $year_filter_id) {
    $tree = [];

    $search_sql = '';
    if (!empty($search_query)) {
        $safe_search = $conn->real_escape_string($search_query);
        $search_sql = " AND (d.file_name LIKE '%{$safe_search}%' OR d.file_code LIKE '%{$safe_search}%')";
    }

    $date_sql = '';
    if (!empty($date_filter)) {
        $safe_date = $conn->real_escape_string($date_filter);
        $date_sql = " AND DATE(d.uploaded_at) = '{$safe_date}'";
    }

    $year_sql = '';
    if (!empty($year_filter_id)) {
        $year_sql = " AND t.id_tahun = " . intval($year_filter_id);
    }

    $add_document = function (&$year_node, $doc, $year, $dept_name) {
        $year_node['children'][] = [
            'id' => 'doc-' . $doc['id'],
            'type' => 'document',
            'name' => $doc['file_name'],
            'file_name' => $doc['file_name'],
            'tahun' => $year,
            'nama_dept' => $dept_name,
            'doc_id' => $doc['id'],
            'upload_at' => $doc['uploaded_at'],
            'update_at' => $doc['updated_at'] ?: '-',
            'upload_by' => $doc['created_by'],
            'kode_file' => $doc['file_code'],
            'is_favorite' => (bool)($doc['is_favorite'] ?? 0)
        ];
    };

    // 1. Public folder: documents marked public in document_permissions.
    if ($dept_filter_id === '' || $dept_filter_id === 'public') {
        $public_node = [
            'id' => 'public',
            'type' => 'folder',
            'name' => 'Public',
            'children' => []
        ];

        $sql_public_years = "SELECT DISTINCT t.id_tahun, t.tahun
                             FROM tahun t
                             JOIN documents d ON d.tahun = t.tahun
                             LEFT JOIN document_permissions dp ON dp.document_id = d.id
                             WHERE d.is_active = 1
                             AND (dp.id IS NULL OR dp.is_private = 0)
                             {$search_sql}
                             {$date_sql}
                             {$year_sql}
                             ORDER BY t.tahun DESC";
        $res_public_years = $conn->query($sql_public_years);

        while ($year = $res_public_years->fetch_assoc()) {
            $year_node = [
                'id' => 'public-year-' . $year['id_tahun'],
                'type' => 'folder',
                'name' => $year['tahun'],
                'children' => []
            ];

            $safe_year = $conn->real_escape_string($year['tahun']);
            $sql_public_docs = "SELECT d.id, d.file_name, d.file_code, d.uploaded_at, d.updated_at, d.created_by, d.nama_dept,
                                       CASE WHEN ufd.document_id IS NULL THEN 0 ELSE 1 END AS is_favorite
                                FROM documents d
                                LEFT JOIN document_permissions dp ON dp.document_id = d.id
                                LEFT JOIN user_favorite_documents ufd ON ufd.document_id = d.id AND ufd.user_id = " . intval($user_id) . "
                                WHERE d.is_active = 1
                                AND d.tahun = '{$safe_year}'
                                AND (dp.id IS NULL OR dp.is_private = 0)
                                {$search_sql}
                                {$date_sql}
                                ORDER BY d.file_name ASC";
            $res_public_docs = $conn->query($sql_public_docs);

            while ($doc = $res_public_docs->fetch_assoc()) {
                $add_document($year_node, $doc, $year['tahun'], $doc['nama_dept']);
            }

            if (!empty($year_node['children'])) {
                $public_node['children'][] = $year_node;
            }
        }

        if (!empty($public_node['children'])) {
            $tree[] = $public_node;
        }
    }

    if ($dept_filter_id === 'public') {
        return $tree;
    }

    // 2. Private folders by department.
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

        // 3. Fetch private years for this department
        $sql_years = "SELECT DISTINCT t.id_tahun, t.tahun 
                      FROM tahun t 
                      JOIN documents d ON d.tahun = t.tahun
                      JOIN document_permissions dp ON dp.document_id = d.id
                      WHERE d.is_active = 1
                      AND dp.is_private = 1
                      AND dp.departement_id = " . intval($dept['id']);
        
        if (!empty($year_filter_id)) {
            $sql_years .= " AND t.id_tahun = " . intval($year_filter_id);
        }
        $sql_years .= $search_sql . $date_sql;
        $sql_years .= " ORDER BY t.tahun DESC";
        $res_years = $conn->query($sql_years);

        while ($year = $res_years->fetch_assoc()) {
            $year_node = [
                'id' => 'year-' . $dept['id'] . '-' . $year['id_tahun'],
                'type' => 'folder',
                'name' => $year['tahun'],
                'children' => []
            ];

            // 4. Fetch private documents for this dept and year
            $sql_docs = "SELECT DISTINCT d.id, d.file_name, d.file_code, d.uploaded_at, d.updated_at, d.created_by, d.nama_dept,
                                CASE WHEN ufd.document_id IS NULL THEN 0 ELSE 1 END AS is_favorite
                         FROM documents d
                         JOIN document_permissions dp ON dp.document_id = d.id
                         LEFT JOIN user_favorite_documents ufd ON ufd.document_id = d.id AND ufd.user_id = " . intval($user_id) . "
                         WHERE d.is_active = 1
                         AND d.tahun = '" . $conn->real_escape_string($year['tahun']) . "'
                         AND dp.is_private = 1
                         AND dp.departement_id = " . intval($dept['id']);
            $sql_docs .= $search_sql . $date_sql . " ORDER BY d.file_name ASC";
            $res_docs = $conn->query($sql_docs);

            while ($doc = $res_docs->fetch_assoc()) {
                $add_document($year_node, $doc, $year['tahun'], $doc['nama_dept']);
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
