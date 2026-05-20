<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
// bug fix hak akses, +manage_documents
$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('manage_documents', $user_permissions) && $_SESSION['role'] !== 'Admin') {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
    header("Location: ../dashboard.php"); // Atau kembali ke dashboard jika login tapi tidak punya akses
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

if (isset($_GET['action']) && in_array($_GET['action'], ['get_details', 'get_versions'])) {
    header('Content-Type: application/json');
    $id = (int) $_GET['id'];

    if ($_GET['action'] === 'get_details' && isset($_GET['type'])) {
        $type = $_GET['type'];
        $response = ['departements' => [], 'users' => []];
        $dept_table = $type . '_departments';
        $id_column = $type . '_id';
        $sql_depts = "SELECT departement_id FROM $dept_table WHERE $id_column = ?";
        $stmt_depts = $conn->prepare($sql_depts);
        $stmt_depts->bind_param("i", $id);
        $stmt_depts->execute();
        $result_depts = $stmt_depts->get_result();
        while ($row = $result_depts->fetch_assoc())
            $response['departements'][] = $row['departement_id'];
        $user_table = $type . '_user_access';
        $sql_users = "SELECT user_id FROM $user_table WHERE $id_column = ?";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("i", $id);
        $stmt_users->execute();
        $result_users = $stmt_users->get_result();
        while ($row = $result_users->fetch_assoc())
            $response['users'][] = $row['user_id'];
        echo json_encode($response);
    } elseif ($_GET['action'] === 'get_versions') {
        $versions = [];
        $sql = "SELECT dv.id, dv.version_file_name, dv.notes, dv.uploaded_at, u.username 
                FROM document_versions dv JOIN users u ON dv.user_id = u.id 
                WHERE dv.document_id = ? ORDER BY dv.uploaded_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc())
            $versions[] = $row;
        echo json_encode($versions);
    }
    exit();
}

// --- LOGIKA POST (Form Submissions) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_success = false;
    $success_message = '';
    $error_message = '';
    $action = $_POST['action'] ?? '';

    $conn->begin_transaction();
    try {
        // Logika untuk membuat folder
        if ($action === 'create_folder') {
            $folder_name = trim($_POST['folder_name']);
            if (!empty($folder_name)) {
                $parent_id = $_POST['parent_id'] === '' ? NULL : (int) $_POST['parent_id'];
                $sql = "INSERT INTO folders (name, parent_id) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $folder_name, $parent_id);
                if ($stmt->execute()) {
                    $new_folder_id = $conn->insert_id;
                    // update_access_rules($conn, $new_folder_id, 'folder', $_POST['departements_access'] ?? [], $_POST['users_access'] ?? []);
                    update_access_rules($conn, $new_folder_id, 'folder', $_POST['departements_access'] ?? [], $_POST['users_access'] ?? []);
                    logActivity($conn, $user_id, 'create_folder', "Admin {$username} membuat folder: " . htmlspecialchars($folder_name), null, $new_folder_id);
                    $action_success = true;
                    $success_message = "<div class='alert alert-success'>Folder berhasil dibuat.</div>";
                }
            }
        }
        // Logika untuk mengunggah PDF
        elseif ($action === 'upload_pdf') {
            $document_title = trim($_POST['document_title']);
            if (!empty($document_title) && isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                $folder_id = $_POST['folder_id'] === '' ? NULL : (int) $_POST['folder_id'];
                $new_file_name = uniqid() . '.' . pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION);
                if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], '../uploads/' . $new_file_name)) {
                    $sql = "INSERT INTO documents (title, file_name, folder_id) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssi", $document_title, $new_file_name, $folder_id);
                    if ($stmt->execute()) {
                        $new_doc_id = $conn->insert_id;
                        // update_access_rules($conn, $new_doc_id, 'document', $_POST['departements_access'] ?? [], $_POST['users_access'] ?? []);
                        logActivity($conn, $user_id, 'upload_document', "Admin {$username} uploading: " . htmlspecialchars($document_title), $new_doc_id);
                        create_notification($conn, 'all', "New documents '" . htmlspecialchars($document_title) . "' have been added.", "dashboard.php?doc_id=" . $new_doc_id, $user_id);
                        $action_success = true;
                        $success_message = "<div class='alert alert-success'>Document uploaded successfully.</div>";
                    }
                }
            }
        }
        // Logika untuk mengganti file dokumen
        elseif ($action === 'replace_document_file') {
            $doc_id = (int) $_POST['doc_id'];
            $version_notes = trim($_POST['version_notes']);

            if (isset($_FILES['new_pdf_file']) && $_FILES['new_pdf_file']['error'] === UPLOAD_ERR_OK) {
                $sql_old_file = "SELECT file_name, title FROM documents WHERE id = ?";
                $stmt_old_file = $conn->prepare($sql_old_file);
                $stmt_old_file->bind_param("i", $doc_id);
                $stmt_old_file->execute();
                $doc_data = $stmt_old_file->get_result()->fetch_assoc();
                $old_file_name = $doc_data['file_name'];
                $doc_title = $doc_data['title'];

                $sql_insert_version = "INSERT INTO document_versions (document_id, version_file_name, notes, user_id) VALUES (?, ?, ?, ?)";
                $stmt_insert_version = $conn->prepare($sql_insert_version);
                $stmt_insert_version->bind_param("issi", $doc_id, $old_file_name, $version_notes, $user_id);
                $stmt_insert_version->execute();

                $new_file_name = uniqid() . '.' . pathinfo($_FILES['new_pdf_file']['name'], PATHINFO_EXTENSION);
                if (move_uploaded_file($_FILES['new_pdf_file']['tmp_name'], '../uploads/' . $new_file_name)) {
                    $sql_update = "UPDATE documents SET file_name = ? WHERE id = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("si", $new_file_name, $doc_id);
                    if ($stmt_update->execute()) {
                        logActivity($conn, $user_id, 'update_document_file', "Admin {$username} memperbarui file untuk dokumen: " . htmlspecialchars($doc_title), $doc_id);
                        create_notification($conn, 'all', "Dokumen '" . htmlspecialchars($doc_title) . "' telah diperbarui.", "dashboard.php?doc_id=" . $doc_id, $user_id);
                        $action_success = true;
                        $success_message = "<div class='alert alert-success'>File dokumen berhasil diperbarui.</div>";
                    }
                }
            }
        }
        // --- FITUR BARU: Membuat Link Berbagi ---
        elseif ($action === 'create_share_link') {
            $doc_id = (int) $_POST['share_doc_id'];
            $password = $_POST['share_password'];
            $expires_at = !empty($_POST['share_expires_at']) ? $_POST['share_expires_at'] : null;
            $token = bin2hex(random_bytes(16));
            $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

            // PASTIKAN BARIS INI MENYERTAKAN "created_by"
            $sql = "INSERT INTO shared_links (document_id, token, password, expires_at, created_by) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);

            // PASTIKAN BARIS INI BERAKHIRAN "i" DAN MEMILIKI VARIABEL $user_id
            $stmt->bind_param("isssi", $doc_id, $token, $hashed_password, $expires_at, $user_id);

            if ($stmt->execute()) {
                $action_success = true;
                $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['PHP_SELF'], 2) . "/share.php?token={$token}";
                $success_message = "<div class='alert alert-success'>Share link created successfully: <input type='text' class='form-control mt-2' value='" . htmlspecialchars($link) . "' readonly></div>";
            }
        }
        // --- FITUR BARU: Aksi Massal (Bulk Actions) ---
        elseif ($action === 'bulk_action') {
            $selected_ids = $_POST['selected_ids'] ?? [];
            $bulk_operation = $_POST['bulk_operation'] ?? '';

            if (!empty($selected_ids) && !empty($bulk_operation)) {
                $item_count = count($selected_ids);
                foreach ($selected_ids as $item) {
                    list($type, $id) = explode('-', $item);
                    $id = (int) $id;

                    if ($bulk_operation === 'delete') {
                        $table = ($type === 'folder') ? 'folders' : 'documents';
                        $stmt_del = $conn->prepare("DELETE FROM $table WHERE id = ?");
                        $stmt_del->bind_param("i", $id);
                        $stmt_del->execute();
                    } elseif ($bulk_operation === 'move') {
                        $new_parent_id = $_POST['bulk_move_folder_id'] === '' ? NULL : (int) $_POST['bulk_move_folder_id'];
                        $table = ($type === 'folder') ? 'folders' : 'documents';
                        $col = ($type === 'folder') ? 'parent_id' : 'folder_id';
                        $stmt = $conn->prepare("UPDATE $table SET $col = ? WHERE id = ?");
                        $stmt->bind_param("ii", $new_parent_id, $id);
                        $stmt->execute();
                    }
                    // Implementasi set_access bisa ditambahkan di sini dengan memanggil fungsi update_access_rules jika ada.
                }
                $action_success = true;
                $success_message = "<div class='alert alert-success'>Aksi massal berhasil diterapkan pada $item_count item.</div>";
            } else {
                $error_message = "<div class='alert alert-warning'>Tidak ada item yang dipilih atau aksi tidak valid.</div>";
            }
        }

        $conn->commit();

    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "<div class='alert alert-danger'>Terjadi kesalahan: " . $e->getMessage() . "</div>";
    }

    if ($action_success) {
        $_SESSION['flash_message'] = $success_message;
    } else {
        $_SESSION['flash_message'] = $error_message ?: "<div class='alert alert-danger'>Terjadi kesalahan atau tidak ada data yang diubah.</div>";
    }
    header("Location: manage_documents.php");
    exit();
}

// LOGIKA GET (Tambahkan 'restore_version')
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'restore_version' && isset($_GET['doc_id']) && isset($_GET['version_id'])) {
        $doc_id = (int) $_GET['doc_id'];
        $version_id = (int) $_GET['version_id'];

        $conn->begin_transaction();
        try {
            $sql_get_version = "SELECT version_file_name FROM document_versions WHERE id = ? AND document_id = ?";
            $stmt_get_version = $conn->prepare($sql_get_version);
            $stmt_get_version->bind_param("ii", $version_id, $doc_id);
            $stmt_get_version->execute();
            $version_to_restore = $stmt_get_version->get_result()->fetch_assoc();

            if (!$version_to_restore)
                throw new Exception("Versi tidak ditemukan.");
            $file_to_restore = $version_to_restore['version_file_name'];

            $sql_get_current = "SELECT file_name, title FROM documents WHERE id = ?";
            $stmt_get_current = $conn->prepare($sql_get_current);
            $stmt_get_current->bind_param("i", $doc_id);
            $stmt_get_current->execute();
            $current_doc = $stmt_get_current->get_result()->fetch_assoc();
            $current_file = $current_doc['file_name'];
            $doc_title = $current_doc['title'];

            $restore_notes = "Pemulihan dari versi yang diunggah sebelumnya.";
            $sql_archive_current = "INSERT INTO document_versions (document_id, version_file_name, notes, user_id) VALUES (?, ?, ?, ?)";
            $stmt_archive_current = $conn->prepare($sql_archive_current);
            $stmt_archive_current->bind_param("issi", $doc_id, $current_file, $restore_notes, $user_id);
            $stmt_archive_current->execute();

            $sql_update_doc = "UPDATE documents SET file_name = ? WHERE id = ?";
            $stmt_update_doc = $conn->prepare($sql_update_doc);
            $stmt_update_doc->bind_param("si", $file_to_restore, $doc_id);
            $stmt_update_doc->execute();

            $sql_delete_version = "DELETE FROM document_versions WHERE id = ?";
            $stmt_delete_version = $conn->prepare($sql_delete_version);
            $stmt_delete_version->bind_param("i", $version_id);
            $stmt_delete_version->execute();

            $conn->commit();
            logActivity($conn, $user_id, 'restore_document_version', "Admin {$username} memulihkan versi dokumen: " . htmlspecialchars($doc_title), $doc_id);
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Dokumen berhasil dipulihkan ke versi yang dipilih.</div>";

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal memulihkan versi: " . $e->getMessage() . "</div>";
        }
        header("Location: manage_documents.php");
        exit();
    }
}

// function buildAdminTree($conn, $parentId = NULL) -> Modified to Flat Structure
function buildAdminTree($conn)
{
    $html = '<ul class="list-group list-group-flush">';
    
    // 1. Fetch Folders (Dept + Tahun)
    $sql_folders = "SELECT f.id_fold, f.id_dep, f.id_tahun, d.name AS dept_name, t.tahun
                    FROM folder f
                    JOIN departements d ON f.id_dep = d.id
                    JOIN tahun t ON f.id_tahun = t.id_tahun
                    ORDER BY t.tahun DESC, d.name ASC";
    $result_folders = $conn->query($sql_folders);

    if ($result_folders && $result_folders->num_rows > 0) {
        while ($row = $result_folders->fetch_assoc()) {
            $folderId = $row['id_fold'];
            $folderName = $row['dept_name'] . ' - ' . $row['tahun'];
            $deptName = $row['dept_name']; // For document matching
            $tahun = $row['tahun'];       // For document matching

            $html .= '<li class="list-group-item folder-item" data-id="' . $folderId . '" data-name="' . htmlspecialchars($folderName) . '">';
            $html .= '<div>
                        <input type="checkbox" class="form-check-input me-2 bulk-checkbox" name="selected_ids[]" form="bulk-action-form" value="folder-' . $folderId . '">
                        <i class="fas fa-folder me-2 text-warning"></i>
                        <span class="folder-name">' . htmlspecialchars($folderName) . '</span>
                      </div>';
            
            // 2. Fetch Documents inside this "Folder" (Matching Dept & Tahun)
            // Note: documents table uses 'nama_dept' and 'tahun'
            $sql_docs = "SELECT id, file_name, file_code FROM documents WHERE nama_dept = ? AND tahun = ? ORDER BY file_name ASC";
            $stmt_docs = $conn->prepare($sql_docs);
            $stmt_docs->bind_param("ss", $deptName, $tahun); // Assuming 'tahun' is string in documents table based on previous usage
            $stmt_docs->execute();
            $result_docs = $stmt_docs->get_result();

            if ($result_docs->num_rows > 0) {
                $html .= '<ul class="list-group list-group-flush ms-4 border-start ps-3">';
                while ($doc = $result_docs->fetch_assoc()) {
                    $docName = $doc['file_name'];
                    // Use file_code + file_name or just file_name
                    $displayName = $doc['file_code'] ? $doc['file_code'] . ' - ' . $docName : $docName;
                    
                    $html .= '<li class="list-group-item document-item border-0 py-1" data-id="' . $doc['id'] . '" data-title="' . htmlspecialchars($docName) . '">';
                    $html .= '<div>
                                <input type="checkbox" class="form-check-input me-2 bulk-checkbox" name="selected_ids[]" form="bulk-action-form" value="doc-' . $doc['id'] . '">
                                <i class="fas fa-file-pdf me-2 text-danger"></i>
                                <span class="document-name">' . htmlspecialchars($displayName) . '</span>
                              </div>
                              </li>';
                }
                $html .= '</ul>';
            }
            $html .= '</li>';
        }
    } else {
        $html .= '<li class="list-group-item text-muted">Belum ada folder.</li>';
    }

    $html .= '</ul>';
    return $html;
}

// function buildFolderOptions($conn, $parentId = NULL, $prefix = '', $excludeFolderId = null) -> Modified to Flat List
function buildFolderOptions($conn, $selectedId = null)
{
    $html = '';
    $sql = "SELECT f.id_fold, d.name AS dept_name, t.tahun
            FROM folder f
            JOIN departements d ON f.id_dep = d.id
            JOIN tahun t ON f.id_tahun = t.id_tahun
            ORDER BY t.tahun DESC, d.name ASC";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $id = $row['id_fold'];
        $name = $row['dept_name'] . ' - ' . $row['tahun'];
        $selected = ($selectedId == $id) ? 'selected' : '';
        $html .= '<option value="' . $id . '" ' . $selected . '>' . htmlspecialchars($name) . '</option>';
    }
    return $html;
}

$departements = [];
$users_list = [];

$sql_dept = "SELECT id, name FROM departements ORDER BY name ASC";
$result_dept = $conn->query($sql_dept);
while ($row = $result_dept->fetch_assoc())
    $departements[] = $row;

$sql_users = "SELECT id, username FROM users ORDER BY username ASC";
$result_users = $conn->query($sql_users);
while ($row = $result_users->fetch_assoc())
    $users_list[] = $row;

?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Document Management & Access Rights</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="../assets/js/theme.js" defer></script>
    <style>
        .select2-container .select2-selection--multiple {
            min-height: 38px !important;
        }

        .folder-tree .list-group-item {
            border: none;
            padding-left: 1.5rem;
        }

        .folder-tree .list-group-item>div {
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .folder-tree ul {
            padding-left: 0;
        }

        .context-menu {
            position: absolute;
            z-index: 1050;
        }

        .folder-tree ul .list-group-item {
            padding-left: 3rem;
        }
    </style>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <h2>Document Management & Access Rights</h2>
        <p class="text-muted">If an item is not given access rights, it will be considered <strong>Public</strong>.
            Right click on an item for options.</p>
        <?= $message ?>
        <div class="row">
            <div class="col-md-7">
                <div class="card mb-4">
                    <div class="card-header">Upload New Document</div>
                    <div class="card-body">
                        <form action="manage_documents.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="upload_pdf">
                            <div class="mb-3"><label class="form-label">Document Title</label><input type="text"
                                    name="document_title" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">Select PDF File</label><input type="file"
                                    name="pdf_file" class="form-control" accept=".pdf" required></div>
                            <div class="mb-3">
                                <label class="form-label">Save in Folder</label>
                                <select name="folder_id" class="form-select">
                                    <option value="">Main Folder</option>
                                    <?= buildFolderOptions($conn); ?>
                                </select>
                            </div>
                            <hr>
                            <h5>Document Access Rights</h5>
                            <div class="mb-3"><label class="form-label">Department Access:</label><select
                                    name="departements_access[]" class="form-select select2"
                                    multiple="multiple"><?php foreach ($departements as $d)
                                        echo "<option value='{$d['id']}'>" . htmlspecialchars($d['name']) . "</option>"; ?></select>
                            </div>
                            <div class="mb-3"><label class="form-label">Specific User Access:</label><select
                                    name="users_access[]" class="form-select select2"
                                    multiple="multiple"><?php foreach ($users_list as $u)
                                        echo "<option value='{$u['id']}'>" . htmlspecialchars($u['username']) . "</option>"; ?></select>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Create New Folder</div>
                    <div class="card-body">
                        <form action="manage_documents.php" method="POST">
                            <input type="hidden" name="action" value="create_folder">
                            <div class="mb-3"><label class="form-label">Folder Name</label><input type="text"
                                    name="folder_name" class="form-control" required></div>
                            <div class="mb-3">
                                <label class="form-label">Save in Folder</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">Main Folder</option>
                                    <?= buildFolderOptions($conn); ?>
                                </select>
                            </div>
                            <hr>
                            <h5>Folder Access Rights</h5>
                            <div class="mb-3"><label class="form-label">Department Access:</label><select
                                    name="departements_access[]" class="form-select select2"
                                    multiple="multiple"><?php foreach ($departements as $d)
                                        echo "<option value='{$d['id']}'>" . htmlspecialchars($d['name']) . "</option>"; ?></select>
                            </div>
                            <div class="mb-3"><label class="form-label">Specific User Access:</label><select
                                    name="users_access[]" class="form-select select2"
                                    multiple="multiple"><?php foreach ($users_list as $u)
                                        echo "<option value='{$u['id']}'>" . htmlspecialchars($u['username']) . "</option>"; ?></select>
                            </div>
                            <button type="submit" class="btn btn-success">Create Folder</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">Folder & Document Structure</div>
                    <div class="card-body">
                        <form action="manage_documents.php" method="POST" id="bulk-action-form">
                            <input type="hidden" name="action" value="bulk_action">
                            <div class="d-flex mb-2">
                                <button type="button" class="btn btn-primary btn-sm" id="bulk-action-btn"
                                    data-bs-toggle="modal" data-bs-target="#bulkActionModal" disabled>
                                    Mass Action for <span id="bulk-item-count-badge"
                                        class="badge bg-light text-dark">0</span> Item
                                </button>
                            </div>
                            <div class="folder-tree border rounded p-2" style="max-height: 600px; overflow-y: auto;">
                                <?= buildAdminTree($conn); ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editFolderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_documents.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Folder</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_folder">
                        <input type="hidden" name="folder_id" id="edit-folder-id">
                        <div class="mb-3"><label class="form-label">Folder Name</label><input type="text"
                                name="new_name" id="edit-folder-name" class="form-control" required></div>
                        <div class="mb-3">
                            <label class="form-label">Move to Folder</label>
                            <select name="new_parent_id" id="edit-folder-parent" class="form-select"></select>
                        </div>
                        <hr>
                        <h5>Modify Access Rights</h5>
                        <div class="mb-3"><label class="form-label">Department Access:</label><select
                                name="departements_access[]" id="edit-folder-depts"
                                class="form-select select2-modal-init"
                                multiple="multiple"><?php foreach ($departements as $d)
                                    echo "<option value='{$d['id']}'>" . htmlspecialchars($d['name']) . "</option>"; ?></select>
                        </div>
                        <div class="mb-3"><label class="form-label">User Access:</label><select name="users_access[]"
                                id="edit-folder-users" class="form-select select2-modal-init"
                                multiple="multiple"><?php foreach ($users_list as $u)
                                    echo "<option value='{$u['id']}'>" . htmlspecialchars($u['username']) . "</option>"; ?></select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Simpan</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editDocumentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_documents.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Document</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_document">
                        <input type="hidden" name="doc_id" id="edit-doc-id">
                        <div class="mb-3"><label class="form-label">Title Document</label><input type="text"
                                name="new_title" id="edit-doc-title" class="form-control" required></div>
                        <div class="mb-3">
                            <label class="form-label">Move to Folder</label>
                            <select name="new_folder_id" id="edit-doc-folder" class="form-select">
                                <option value="">Main Folder</option>
                                <?= buildFolderOptions($conn); ?>
                            </select>
                        </div>
                        <hr>
                        <h5>Modify Access Rights</h5>
                        <div class="mb-3"><label class="form-label">Department Access:</label><select
                                name="departements_access[]" id="edit-doc-depts" class="form-select select2-modal-init"
                                multiple="multiple"><?php foreach ($departements as $d)
                                    echo "<option value='{$d['id']}'>" . htmlspecialchars($d['name']) . "</option>"; ?></select>
                        </div>
                        <div class="mb-3"><label class="form-label">User Access:</label><select name="users_access[]"
                                id="edit-doc-users" class="form-select select2-modal-init"
                                multiple="multiple"><?php foreach ($users_list as $u)
                                    echo "<option value='{$u['id']}'>" . htmlspecialchars($u['username']) . "</option>"; ?></select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="replaceDocumentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="manage_documents.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Document File</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="replace_document_file">
                        <input type="hidden" name="doc_id" id="replace-doc-id">
                        <div class="mb-3"><label class="form-label">Select New PDF File</label><input type="file"
                                name="new_pdf_file" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Version Notes (Ex: Revised Chapter
                                2)</label><textarea name="version_notes" class="form-control" rows="3"
                                placeholder="Explain the changes..."></textarea></div>
                        <p class="text-muted small">Old files will be archived as version history.</p>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Batal</button><button type="submit"
                            class="btn btn-primary">Update</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewVersionsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewVersionsModalLabel">Document Version History</h5><button
                        type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 id="version-modal-title"></h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>By</th>
                                    <th>Notes</th>
                                    <th>Archive Files</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="version-history-body"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shareDocumentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="manage_documents.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Share Link</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_share_link">
                        <input type="hidden" name="share_doc_id" id="share-doc-id">
                        <p>Anda akan membuat tautan untuk: <strong id="share-doc-title"></strong></p>
                        <div class="mb-3"><label for="share_password" class="form-label">Password
                                (Optional)</label><input type="text" name="share_password" id="share_password"
                                class="form-control"></div>
                        <div class="mb-3"><label for="share_expires_at" class="form-label">Expiration
                                (Optional)</label><input type="datetime-local" name="share_expires_at"
                                id="share_expires_at" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Buat
                            Tautan</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="bulkActionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mass Action</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><span id="bulk-item-count">0</span> selected items.</p>
                    <div class="mb-3">
                        <label for="bulk_operation" class="form-label">Select Action</label>
                        <select id="bulk_operation" name="bulk_operation" form="bulk-action-form" class="form-select">
                            <option value="" selected disabled>-- Select Action--</option>
                            <option value="move">Move</option>
                            <option value="set_access">Change Access Rights</option>
                            <option value="delete">Delete</option>
                        </select>
                    </div>
                    <div id="bulk-action-fields">
                        <div id="bulk-move-field" class="d-none mb-3">
                            <label class="form-label">Move to Folder</label>
                            <select name="bulk_move_folder_id" form="bulk-action-form" class="form-select">
                                <option value="">Main Folder (Root)</option>
                                <?= buildFolderOptions($conn); ?>
                            </select>
                        </div>
                        <div id="bulk-set_access-field" class="d-none">
                            <hr>
                            <h5>Change Access Rights (will overwrite old ones)</h5>
                            <div class="mb-3"><label class="form-label">Department Access:</label><select
                                    name="bulk_departements_access[]" form="bulk-action-form"
                                    class="form-select select2-modal-init"
                                    multiple="multiple"><?php foreach ($departements as $d)
                                        echo "<option value='{$d['id']}'>" . htmlspecialchars($d['name']) . "</option>"; ?></select>
                            </div>
                            <div class="mb-3"><label class="form-label">User Access:</label><select
                                    name="bulk_users_access[]" form="bulk-action-form"
                                    class="form-select select2-modal-init"
                                    multiple="multiple"><?php foreach ($users_list as $u)
                                        echo "<option value='{$u['id']}'>" . htmlspecialchars($u['username']) . "</option>"; ?></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-primary"
                        id="apply-bulk-action">Apply</button></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        $(document).ready(function () {
            $('.select2').select2({ placeholder: "Select one or more", allowClear: true, width: '100%' });

            const initModalSelect2 = (modalId) => {
                const modalElement = $(`#${modalId}`);
                modalElement.find('.select2-modal-init').each(function () {
                    if ($(this).data('select2')) $(this).select2('destroy');
                });
                modalElement.find('.select2-modal-init').select2({
                    placeholder: "Select one or more",
                    allowClear: true,
                    width: '100%',
                    dropdownParent: modalElement
                });
            };

            const folderTree = document.querySelector('.folder-tree');
            let contextMenu = null;

            $(document).on('click', function () { if (contextMenu) contextMenu.remove(); });

            folderTree.addEventListener('contextmenu', function (event) {
                event.preventDefault();
                event.stopPropagation();
                if (contextMenu) contextMenu.remove();

                const targetDiv = event.target.closest('div');
                if (!targetDiv) return;
                const target = targetDiv.parentElement;
                if (!target.classList.contains('list-group-item')) return;

                const id = target.dataset.id;
                const title = target.dataset.title || '';
                let menuHtml = '<ul class="dropdown-menu d-block">';

                if (target.classList.contains('folder-item')) {
                    menuHtml += `<li><a class="dropdown-item" href="#" data-action="edit-folder" data-id="${id}"><i class="fas fa-edit me-2"></i>Edit Folder</a></li>`;
                } else if (target.classList.contains('document-item')) {
                    menuHtml += `<li><a class="dropdown-item" href="#" data-action="edit-document" data-id="${id}"><i class="fas fa-edit me-2"></i>Edit Details & Access</a></li>`;
                    menuHtml += `<li><a class="dropdown-item" href="#" data-action="replace-document" data-id="${id}"><i class="fas fa-file-import me-2"></i>Update File</a></li>`;
                    menuHtml += `<li><a class="dropdown-item" href="#" data-action="view-versions" data-id="${id}" data-title="${title}"><i class="fas fa-history me-2"></i>Version History</a></li>`;
                    menuHtml += `<li><a class="dropdown-item" href="#" data-action="share-document" data-id="${id}" data-title="${title}"><i class="fas fa-share-alt me-2"></i>Create Share Link</a></li>`;
                }

                menuHtml += `<li><hr class="dropdown-divider"></li>`;
                const deleteAction = target.classList.contains('folder-item') ? 'delete_folder' : 'delete_document';
                menuHtml += `<li><a class="dropdown-item text-danger" href="manage_documents.php?action=${deleteAction}&id=${id}" onclick="return confirm('Yakin hapus item ini?');"><i class="fas fa-trash me-2"></i>Delete</a></li>`;
                menuHtml += '</ul>';

                contextMenu = document.createElement('div');
                contextMenu.className = 'context-menu';
                contextMenu.innerHTML = menuHtml;
                contextMenu.style.top = `${event.pageY}px`;
                contextMenu.style.left = `${event.pageX}px`;
                document.body.appendChild(contextMenu);
            });

            $(document).on('click', '.context-menu a', function (e) {
                e.preventDefault();
                const action = $(this).data('action');
                const id = $(this).data('id');
                const title = $(this).data('title');

                if (action === 'edit-folder') {
                    const modalId = 'editFolderModal';
                    const modal = new bootstrap.Modal(document.getElementById(modalId));
                    $('#edit-folder-id').val(id);
                    $('#edit-folder-name').val($(`li[data-id='${id}']`).data('name'));

                    const selectElement = $('#edit-folder-parent');
                    selectElement.html('<option value="">Folder Utama</option>');
                    const folderOptionsHtml = '<?= addslashes(buildFolderOptions($conn, null, "", "EXCLUDE_ID_PLACEHOLDER")); ?>';
                    selectElement.append(folderOptionsHtml.replace(/EXCLUDE_ID_PLACEHOLDER/g, id));
                    selectElement.val($(`li[data-id='${id}']`).data('parent-id'));

                    initModalSelect2(modalId);
                    $.getJSON(`manage_documents.php?action=get_details&type=folder&id=${id}`, function (data) {
                        $('#edit-folder-depts').val(data.departements).trigger('change');
                        $('#edit-folder-users').val(data.users).trigger('change');
                        modal.show();
                    });
                } else if (action === 'edit-document') {
                    const modalId = 'editDocumentModal';
                    initModalSelect2(modalId);
                    $.getJSON(`manage_documents.php?action=get_details&type=document&id=${id}`, function (data) {
                        $('#edit-doc-id').val(id);
                        $('#edit-doc-title').val($(`li[data-id='${id}']`).data('title'));
                        $('#edit-doc-folder').val($(`li[data-id='${id}']`).data('folder-id'));
                        $('#edit-doc-depts').val(data.departements).trigger('change');
                        $('#edit-doc-users').val(data.users).trigger('change');
                        new bootstrap.Modal(document.getElementById(modalId)).show();
                    });
                } else if (action === 'replace-document') {
                    $('#replace-doc-id').val(id);
                    new bootstrap.Modal(document.getElementById('replaceDocumentModal')).show();
                } else if (action === 'view-versions') {
                    const modal = new bootstrap.Modal(document.getElementById('viewVersionsModal'));
                    $('#version-modal-title').text('Riwayat untuk: ' + title);
                    const tbody = $('#version-history-body');
                    tbody.html('<tr><td colspan="5" class="text-center">Memuat...</td></tr>');
                    modal.show();
                    $.getJSON(`manage_documents.php?action=get_versions&id=${id}`, function (versions) {
                        tbody.empty();
                        if (versions.length > 0) {
                            versions.forEach(v => {
                                const notes = v.notes ? v.notes.replace(/\n/g, '<br>') : '<em class="text-muted">Tidak ada catatan</em>';
                                const row = `<tr>
                                    <td>${new Date(v.uploaded_at).toLocaleString('id-ID')}</td>
                                    <td>${v.username}</td>
                                    <td>${notes}</td>
                                    <td>${v.version_file_name}</td>
                                    <td><a href="manage_documents.php?action=restore_version&doc_id=${id}&version_id=${v.id}" class="btn btn-sm btn-outline-primary" onclick="return confirm('Yakin memulihkan versi ini?');">Pulihkan</a></td>
                                </tr>`;
                                tbody.append(row);
                            });
                        } else {
                            tbody.html('<tr><td colspan="5" class="text-center">Tidak ada riwayat versi.</td></tr>');
                        }
                    });
                } else if (action === 'share-document') {
                    $('#share-doc-id').val(id);
                    $('#share-doc-title').text(title);
                    $('#share_password').val('');
                    $('#share_expires_at').val('');
                    new bootstrap.Modal(document.getElementById('shareDocumentModal')).show();
                }
            });

            $('.bulk-checkbox').on('change', function () {
                const checkedCount = $('.bulk-checkbox:checked').length;
                $('#bulk-action-btn').prop('disabled', checkedCount === 0);
                $('#bulk-item-count-badge').text(checkedCount);
                $('#bulk-item-count').text(checkedCount);
            });

            $('#bulk_operation').on('change', function () {
                const selectedAction = $(this).val();
                $('#bulk-action-fields > div').addClass('d-none');
                if (selectedAction) {
                    $(`#bulk-${selectedAction}-field`).removeClass('d-none');
                }
                if (selectedAction === 'set_access') {
                    initModalSelect2('bulkActionModal');
                }
            });

            $('#apply-bulk-action').on('click', function (e) {
                e.preventDefault();
                const op = $('#bulk_operation').val();
                if (!op) {
                    alert('Silakan pilih aksi yang akan dilakukan.');
                    return;
                }
                if (op === 'delete' && !confirm('Anda yakin ingin menghapus semua item yang dipilih? Aksi ini tidak dapat dibatalkan.')) {
                    return;
                }
                $('#bulk-action-form').submit();
            });

            $('.folder-item > div').on('click', function (e) {
                if (e.target.type !== 'checkbox') {
                    $(this).parent().children('ul').slideToggle('fast');
                    $(this).find('.fa-folder').toggleClass('text-warning fa-folder-open');
                }
            });
        });
    </script>
</body>

</html>