<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Check login

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check permission
$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('manage_documentsrev', $user_permissions) && $_SESSION['role'] !== 'Admin') {
    header("Location: ../dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

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

$tahun = [];
$sql_tahun = "
    SELECT DISTINCT t.id_tahun, t.tahun 
    FROM tahun t 
    JOIN documents d ON d.tahun = t.tahun 
    ORDER BY t.tahun DESC
";
$result_tahun = $conn->query($sql_tahun);
while ($row = $result_tahun->fetch_assoc())
    $tahun[] = $row;


//filter tahun
$tahun_filter = $_GET['tahun'] ?? '';
$dept_filter = $_GET['nama_dept'] ?? '';

// Folder filters (Modal)
$f_dept = $_GET['f_dept'] ?? '';
$f_tahun = $_GET['f_tahun'] ?? '';

/* =========================
   Query data dokumen
========================= */
// $sql = "SELECT * FROM documents";
$sql = "SELECT a.*, d.id AS dept_id, t.id_tahun AS tahun_id 
        FROM documents a
        LEFT JOIN departements d ON a.nama_dept = d.name
        LEFT JOIN tahun t ON a.tahun = t.tahun
        WHERE a.is_active = 0";
$params = [];
$types = "";

if (!empty($tahun_filter)) {
    $params[] = $tahun_filter;
    $types .= "s";
}

if (!empty($dept_filter)) {
    $params[] = $dept_filter;
    $types .= "s";
}

if (!empty($params)) {
    $sql .= " AND ";
    if (!empty($tahun_filter) && !empty($dept_filter)) {
        $sql .= "a.tahun = ? AND a.nama_dept = ?";
    } elseif (!empty($tahun_filter)) {
        $sql .= "a.tahun = ?";
    } else {
        $sql .= "a.nama_dept = ?";
    }
}

$sql .= " ORDER BY a.uploaded_at DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// --- AJAX HANDLERS FOR CONTEXT MENU ---
if (isset($_GET['action']) && in_array($_GET['action'], ['get_details', 'get_versions'])) {
    header('Content-Type: application/json');
    $id = (int)$_GET['id'];
    
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
        while($row = $result_depts->fetch_assoc()) $response['departements'][] = $row['departement_id'];
        
        $user_table = $type . '_user_access';
        $sql_users = "SELECT user_id FROM $user_table WHERE $id_column = ?";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("i", $id);
        $stmt_users->execute();
        $result_users = $stmt_users->get_result();
        while($row = $result_users->fetch_assoc()) $response['users'][] = $row['user_id'];
        echo json_encode($response);
        exit();
    } elseif ($_GET['action'] === 'get_versions') {
        $versions = [];
        $sql = "SELECT dv.id, dv.version_file_name, dv.notes, dv.uploaded_at, u.username 
                FROM document_versions dv JOIN users u ON dv.user_id = u.id 
                WHERE dv.document_id = ? ORDER BY dv.uploaded_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result_v = $stmt->get_result();
        while($row = $result_v->fetch_assoc()) $versions[] = $row;
        echo json_encode($versions);
        exit();
    }
}

// RESTORE VERSION HANDLER
if (isset($_GET['action']) && $_GET['action'] === 'restore_version') {
    $doc_id = (int)$_GET['doc_id'];
    $version_id = (int)$_GET['version_id'];
    try {
        $conn->begin_transaction();
        $res_v = $conn->query("SELECT version_file_name FROM document_versions WHERE id = $version_id");
        $version = $res_v->fetch_assoc();
        
        $res_d = $conn->query("SELECT file_name, file_path, tahun, nama_dept FROM documents WHERE id = $doc_id");
        $doc = $res_d->fetch_assoc();
        
        // Save current as version
        $stmt_v = $conn->prepare("INSERT INTO document_versions (document_id, version_file_name, notes, user_id) VALUES (?, ?, 'Restored from previous version', ?)");
        $stmt_v->bind_param("isi", $doc_id, $doc['file_name'], $user_id);
        $stmt_v->execute();
        
        // Update document with old version file
        $new_path = "../uploads/{$doc['tahun']}/{$doc['nama_dept']}/" . $version['version_file_name'];
        $stmt_u = $conn->prepare("UPDATE documents SET file_name = ?, file_path = ? WHERE id = ?");
        $stmt_u->bind_param("ssi", $version['version_file_name'], $new_path, $doc_id);
        $stmt_u->execute();
        
        $conn->query("DELETE FROM document_versions WHERE id = $version_id");
        $conn->commit();
        $_SESSION['flash_message'] = "<div class='alert alert-success'>Versi berhasil dipulihkan.</div>";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal memulihkan versi: " . $e->getMessage() . "</div>";
    }
    header("Location: manage_documentsrevbaru.php");
    exit();
}

// POST HANDLERS FOR SHARE AND REPLACE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_share_link') {
        header('Content-Type: application/json');
        $doc_id = (int)$_POST['share_doc_id'];
        $password = $_POST['share_password'];
        $expires = $_POST['share_expires_at'] ?: null;
        $token = bin2hex(random_bytes(16));
        $hashed = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
        
        $stmt = $conn->prepare("INSERT INTO shared_links (document_id, token, password, expires_at, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $doc_id, $token, $hashed, $expires, $user_id);
        if ($stmt->execute()) {
            $link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}" . dirname($_SERVER['PHP_SELF'], 2) . "/share.php?token=$token";
            echo json_encode(['status' => 'success', 'link' => $link]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit();
    }
    
    if ($_POST['action'] === 'replace_document_file') {
         $doc_id = (int)$_POST['doc_id'];
         $notes = $_POST['version_notes'];
         if (isset($_FILES['new_pdf_file']) && $_FILES['new_pdf_file']['error'] === 0) {
             $res_d = $conn->query("SELECT file_name, file_path, tahun, nama_dept FROM documents WHERE id = $doc_id");
             $doc = $res_d->fetch_assoc();
             
             // Archive current
             $stmt_v = $conn->prepare("INSERT INTO document_versions (document_id, version_file_name, notes, user_id) VALUES (?, ?, ?, ?)");
             $stmt_v->bind_param("issi", $doc_id, $doc['file_name'], $notes, $user_id);
             $stmt_v->execute();
             
             // Upload new
             $ext = pathinfo($_FILES['new_pdf_file']['name'], PATHINFO_EXTENSION);
             $new_filename = pathinfo($doc['file_name'], PATHINFO_FILENAME) . '_' . time() . '.' . $ext;
             $target_dir = "../uploads/{$doc['tahun']}/{$doc['nama_dept']}/";
             if (move_uploaded_file($_FILES['new_pdf_file']['tmp_name'], $target_dir . $new_filename)) {
                 $new_path = $target_dir . $new_filename;
                 $conn->query("UPDATE documents SET file_name = '$new_filename', file_path = '$new_path' WHERE id = $doc_id");
                 $_SESSION['flash_message'] = "<div class='alert alert-success'>File berhasil diperbarui.</div>";
             }
         }
         header("Location: manage_documentsrevbaru.php");
         exit();
    }
}

// RESTORE DOCUMENT HANDLER (GET)
if (isset($_GET['action']) && $_GET['action'] === 'restore_document') {
    $id = (int)$_GET['id'];
    try {
        $stmt = $conn->prepare("UPDATE documents SET is_active = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Dokumen berhasil dipulihkan.</div>";
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal memulihkan dokumen: " . $e->getMessage() . "</div>";
    }
    header("Location: restore.php");
    exit();
}

// DELETE PERMANENT DOCUMENT HANDLER (GET)
if (isset($_GET['action']) && $_GET['action'] === 'delete_document') {
    $id = (int)$_GET['id'];
    try {
        $stmt = $conn->prepare("SELECT file_path FROM documents WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($doc = $res->fetch_assoc()) {
            if (file_exists($doc['file_path'])) unlink($doc['file_path']);
            
            // Delete associated records
            $conn->query("DELETE FROM document_versions WHERE document_id = $id");
            $conn->query("DELETE FROM shared_links WHERE document_id = $id");
            $conn->query("DELETE FROM document_departments WHERE document_id = $id");
            $conn->query("DELETE FROM document_user_access WHERE document_id = $id");
            $conn->query("DELETE FROM documents WHERE id = $id");
            
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Dokumen berhasil dihapus secara permanen.</div>";
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal menghapus dokumen: " . $e->getMessage() . "</div>";
    }
    header("Location: restore.php");
    exit();
}

/* =========================
   Ambil list tahun (dropdown)
========================= */
$tahunList = mysqli_query(
    $conn,
    "SELECT DISTINCT tahun FROM documents ORDER BY tahun DESC"
);
$deptList = mysqli_query(
    $conn,
    "SELECT DISTINCT nama_dept FROM documents ORDER BY nama_dept DESC"
);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Documents (REMAKE) - iPortal</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">

    <!-- CSS Dependencies -->
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/theme.js" defer></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <style>
        .main-content {
            padding: 2rem 0;
            min-height: calc(100vh - 100px);
        }

        .file-name {
            font-weight: 500;
            color: #0d6efd;
            cursor: pointer;
        }

        /* Context Menu Styles */
        .context-menu {
            position: absolute;
            z-index: 10000;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 8px;
            overflow: hidden;
            min-width: 180px;
        }
        .context-menu .dropdown-menu {
            position: static;
            display: block;
            border: none;
            margin: 0;
            padding: 4px 0;
        }
        .context-menu .dropdown-item {
            padding: 8px 16px;
            font-size: 13px;
        }
        .context-menu .dropdown-item i {
            width: 1.25rem;
            text-align: center;
        }

        .badge-dept {
            background-color: #e7f1ff;
            color: #0d6efd;
            font-weight: 500;
        }

        .remake-placeholder {
            border: 2px dashed #ccc;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            color: #6c757d;
            background: #f8f9fa;
        }

        @media all and (min-width: 992px) {
            .dropdown-menu li {
                position: relative;
            }

            .nav-item .submenu {
                display: none;
                position: absolute;
                left: 100%;
                top: -7px;
            }

            .nav-item .submenu-left {
                right: 100%;
                left: auto;
            }

            .dropdown-menu>li:hover {
                background-color: #f1f1f1
            }

            .dropdown-menu>li:hover>.submenu {
                display: block;
            }
        }

        /* ============ desktop view .end// ============ */

        /* ============ small devices ============ */
        @media (max-width: 991px) {
            .dropdown-menu .dropdown-menu {
                margin-left: 0.7rem;
                margin-right: 0.7rem;
                margin-bottom: .5rem;
            }
        }

        .card {
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
            transition: 0.3s;
            border-radius: 5px;
            /* 5px rounded corners */
        }

        /* Add rounded corners to the top left and the top right corner of the image */
        img {
            border-radius: 5px 5px 0 0;
        }

        .parent {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            grid-template-rows: 1fr; /* Use 1fr to fill height */
            gap: 4px;
            height: calc(100vh - 120px); /* Fixed height for viewport */
            overflow: hidden; /* Prevent body scroll */
        }

        .div1 {
            border-radius:10px;
            grid-column: span 1;
            background: #fdfdfd;
            border-right: 1px solid #eee;
            padding-right: 5px;
            padding-left:5px;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Important for inner scroll */
        }

        .div2 {
            grid-column: span 2;
            height: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .div3 {
            border-radius:10px;
            grid-column: span 4/span 4;
            grid-column-start: 4;
            background: #fdfdfd;
            border-left: 1px solid #eee;
            padding-left: 10px;
            height: 100%;
            overflow: hidden;
        }

        /* Folder styling */
        .folder-item {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            text-decoration: none;
            color: #444;
            margin-bottom: 5px;
        }

        .folder-item:hover {
            background: #eef2f7;
            color: #0d6efd;
            transform: translateX(4px);
        }

        .folder-item.active {
            background: #0d6efd;
            color: #fff;
        }

        .folder-item i {
            margin-right: 10px;
            font-size: 1.1rem;
            color: #ffc107;
        }

        .folder-item.active i {
            color: #fff;
        }

        .folder-title {
            font-size: 0.9rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container-fluid">
    <div class="card mt-2">
        <div class="card-header">
            <h4 class="card-title" style="text-align: center;">Restore Dokumen</h4>
            <h6 class="card-title" style="text-align: center;">Ini adalah halaman untuk restore data</h6>
        </div>
        <div class="card-body">
            <div class="row" style="margin-bottom: 1rem; justify-content: center;">
                <div class="col-3">
                    <form method="GET" id="filterForm" class="d-flex gap-2">
                        <select name="tahun" class="form-select" onchange="this.form.submit()">
                            <option value="">Pilih Tahun</option>
                            <?php
                            mysqli_data_seek($tahunList, 0);
                            while ($row = mysqli_fetch_assoc($tahunList)):
                            ?>
                                <option value="<?= htmlspecialchars($row['tahun']) ?>" <?= ($row['tahun'] == $tahun_filter) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['tahun']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                </div>
                <div class="col-3">
                        <select name="nama_dept" class="form-select" onchange="this.form.submit()">
                            <option value="">Pilih Departemen</option>
                            <?php
                            mysqli_data_seek($deptList, 0);
                            while ($row = mysqli_fetch_assoc($deptList)):
                            ?>
                                <option value="<?= htmlspecialchars($row['nama_dept']) ?>" <?= ($row['nama_dept'] == $dept_filter) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['nama_dept']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </form>
                </div>
            </div>
            <div class="row">
               <div class="col-12" style="justify-content: center;" > 
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama File</th>
                            <th>Departemen</th>
                            <th>Tahun</th>
                            <th>Tanggal Upload</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($result) > 0):
                            while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-file-pdf text-danger me-2"></i>
                                        <span><?= htmlspecialchars($row['file_name']) ?></span>
                                    </div>
                                    <small class="text-muted"><?= htmlspecialchars($row['file_code']) ?></small>
                                </td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['nama_dept']) ?></span></td>
                                <td><?= htmlspecialchars($row['tahun']) ?></td>
                                <td><?= date('d M Y H:i', strtotime($row['uploaded_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?action=restore_document&id=<?= $row['id'] ?>" 
                                           class="btn btn-success" 
                                           onclick="return confirm('Apakah Anda yakin ingin memulihkan dokumen ini?')">
                                            <i class="fas fa-undo me-1"></i> Restore
                                        </a>
                                        <a href="?action=delete_document&id=<?= $row['id'] ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirm('PERINGATAN: Dokumen akan dihapus secara permanen! Lanjutkan?')">
                                            <i class="fas fa-trash me-1"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php 
                            endwhile; 
                        else:
                        ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-trash-restore fa-3x mb-3 d-block"></i>
                                    Tidak ada dokumen di tempat sampah.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
            </div>

        </div>
        <div class="card-footer">
            <small class="text-muted">Last updated 3 mins ago</small>
        </div>
    </div>
    </div>
    <!-- Modal PDF Viewer -->
    <div class="modal fade" id="pdfModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">PDF Viewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0" style="height:80vh;">
                    <iframe id="pdfIframe" src="" style="width:100%; height:100%; border:none;" allowfullscreen>
                    </iframe>
                </div>

            </div>
        </div>
    </div>
    <!-- Modal Tahun -->
    <div class="modal fade" id="CreateTahunModal" tabindex="-1" aria-labelledby="CreateTahunModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="CreateTahunModalLabel">Buat Folder Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="create_tahun_form" action="create_tahun.php" method="POST">
                        <input type="hidden" name="action" value="create_tahun">
                        <div class="form-group mb-3">
                            <label class="form-label">Tahun</label>
                            <input type="text" class="form-control" name="tahun_input" id="tahun_input"
                                placeholder="Tahun" required>
                        </div>

                        <hr>
                        <h6>Tahun Yang Sudah ada</h6>
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Tahun</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_existing = "SELECT id_tahun, tahun FROM tahun ORDER BY tahun DESC";
                                $res_existing = $conn->query($sql_existing);
                                if ($res_existing && $res_existing->num_rows > 0) {
                                    while ($row_ex = $res_existing->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row_ex['tahun']) . "</td>";
                                        echo "<td>";
                                        echo "<a class='text-danger delete-tahun-btn' href='#' data-tahun-id='" . $row_ex['id_tahun'] . "' data-tahun='" . htmlspecialchars($row_ex['tahun']) . "'><i class='bi bi-trash'></i> Delete</a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='2' class='text-center'>Belum ada data tahun.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button form="create_tahun_form" type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Create Folder Modal -->
    <div class="modal fade" id="CreateFolderModal" tabindex="-1" aria-labelledby="CreateFolderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="CreateFolderModalLabel">Buat Folder Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="create_folder_form" action="create_folder.php" method="POST">
                        <input type="hidden" name="action" value="create_folder">
                        <div class="form-group mb-3">
                            <label class="form-label">Departemen (Auto-fill Name)</label>
                            <select name="department_id" id="folder_dept_select" class="form-select">
                                <option value="">-- Pilih Departemen --</option>
                                <?php foreach ($departements as $d): ?>
                                    <option value="<?= htmlspecialchars($d['id']) ?>"
                                        data-name="<?= htmlspecialchars($d['name']) ?>">
                                        <?= htmlspecialchars($d['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Pilih departemen untuk menggunakan nama departemen sebagai
                                nama
                                folder.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="form-label">Nama Folder</label>
                            <input type="text" class="form-control" name="nama_dep" id="nama_dep"
                                placeholder="Nama Folder" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Tahun</label>
                            <select name="nama_tahun" id="nama_tahun" class="form-select" required>
                                <option value="">Pilih Tahun</option>
                                <?php
                                $query1 = mysqli_query($conn, "SELECT * FROM tahun");
                                while ($row1 = mysqli_fetch_array($query1)) {
                                    echo '<option value="' . $row1['id_tahun'] . '">' . $row1['tahun'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button form="create_folder_form" type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                        <hr>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <select name="f_dept" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Departemen --</option>
                                    <?php foreach ($departements as $d): ?>
                                        <option value="<?= htmlspecialchars($d['id']) ?>" <?= ($f_dept == $d['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($d['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <select name="f_tahun" class="form-select" onchange="this.form.submit()">
                                    <option value="">-- Semua Tahun --</option>
                                    <?php foreach ($tahun as $t): ?>
                                        <option value="<?= htmlspecialchars($t['id_tahun']) ?>"
                                            <?= ($f_tahun == $t['id_tahun']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($t['tahun']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Dept.</th>
                                    <th>Tahun</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_existing = "SELECT a.*, b.name, t.tahun 
                                                FROM folder a
                                                JOIN departements b ON a.id_dep = b.id 
                                                JOIN tahun t ON a.id_tahun = t.id_tahun 
                                                WHERE 1=1";
                                if (!empty($f_dept))
                                    $sql_existing .= " AND a.id_dep = '" . $conn->real_escape_string($f_dept) . "'";
                                if (!empty($f_tahun))
                                    $sql_existing .= " AND a.id_tahun = '" . $conn->real_escape_string($f_tahun) . "'";
                                $sql_existing .= " ORDER BY a.id_fold DESC";
                                $res_existing = $conn->query($sql_existing);//ganti dari anti
                                if ($res_existing && $res_existing->num_rows > 0) {
                                    while ($row_ex = $res_existing->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row_ex['name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row_ex['tahun']) . "</td>";
                                        echo "<td>";
                                        echo "<a class='text-danger delete-folder-btn' href='#' data-folder-id='" . $row_ex['id_fold'] . "' data-folder='" . htmlspecialchars($row_ex['name']) . "'><i class='bi bi-trash'></i> Delete</a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='2' class='text-center'>Belum ada data tahun.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Upload File Modal -->
    <div class="modal fade" id="UploadFileModal" tabindex="-1" aria-labelledby="UploadFileModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="UploadFileModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="upload" action="upload_documents.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="">Kode File</label>
                            <input type="text" class="form-control" placeholder="Masukkan Kode File" name="file_code"
                                required="required">
                        </div>
                        <div class="form-group">
                            <label for="">Nama File</label>
                            <input type="text" class="form-control" placeholder="Masukkan Nama File" name="file_name"
                                required="required">
                        </div>
                        <div class="form-group">
                            <label for="">Departemen</label>
                            <select name="department_id" id="" class="form-select">
                                <option value="">Select Department</option>
                                <?php foreach ($departements as $d)
                                    echo "<option value='{$d['id']}'>" . htmlspecialchars($d['name']) . "</option>"; ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>Akses Departemen (Multi)</label>
                            <select name="departements_access[]" id="upload_departements_access" class="form-control select2-access" multiple="multiple" style="width: 100%;">
                                <?php foreach ($departements as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>Akses User Spesifik (Multi)</label>
                            <select name="users_access[]" id="upload_users_access" class="form-control select2-access" multiple="multiple" style="width: 100%;">
                                <?php foreach ($users_list as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Tahun</label>
                            <select name="tahun" class="form-select" required>
                                <option value="">Pilih Tahun</option>
                                <?php
                                $query1 = mysqli_query($conn, "SELECT * FROM tahun");
                                while ($row1 = mysqli_fetch_array($query1)) {
                                    echo '<option value="' . $row1['id_tahun'] . '">' . $row1['tahun'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group mt-3">
                            <label for="">Pilih File</label>
                            <input type="file" class="form-control" name="file" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button form="upload" type="submit" class="btn btn-primary" name="upload" value="upload">Save
                        changes</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Edit Document Modal -->
    <div class="modal fade" id="EditFileModal" tabindex="-1" aria-labelledby="EditFileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="EditFileModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit
                        Dokumen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm" action="update_documents.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="doc_id" id="edit_doc_id">
                        <div class="mb-3">
                            <label class="form-label">Kode File</label>
                            <input type="text" class="form-control" name="file_code" id="edit_file_code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama File</label>
                            <input type="text" class="form-control" name="file_name" id="edit_file_name" required>
                            <small class="text-muted">Nama ini akan digunakan untuk rename file di server.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Departemen</label>
                            <select name="department_id" id="edit_department_id" class="form-select" required>
                                <option value="">Pilih Departemen</option>
                                <?php foreach ($departements as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Akses Departemen</label>
                            <select name="departements_access[]" id="edit_departements_access" class="form-control select2-access" multiple="multiple" style="width: 100%;">
                                <?php foreach ($departements as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Akses User Spesifik</label>
                            <select name="users_access[]" id="edit_users_access" class="form-control select2-access" multiple="multiple" style="width: 100%;">
                                <?php foreach ($users_list as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" id="edit_tahun_id" class="form-select" required>
                                <option value="">Pilih Tahun</option>
                                <?php
                                $query_t = mysqli_query($conn, "SELECT * FROM tahun ORDER BY tahun DESC");
                                while ($rt = mysqli_fetch_array($query_t)) {
                                    echo '<option value="' . $rt['id_tahun'] . '">' . $rt['tahun'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ganti File (Optional)</label>
                            <input type="file" class="form-control" name="file">
                            <small class="text-info">Biarkan kosong jika tidak ingin mengganti file PDF.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button form="editForm" type="submit" name="updateDocument" class="btn btn-success">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- View Versions Modal -->
    <div class="modal fade" id="viewVersionsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="version-modal-title">Riwayat Versi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Oleh</th>
                                    <th>Catatan</th>
                                    <th>File</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="version-history-body">
                                <!-- Data populated via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Share Document Modal -->
    <div class="modal fade" id="shareDocumentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="shareForm" method="POST">
                    <input type="hidden" name="action" value="create_share_link">
                    <input type="hidden" name="share_doc_id" id="share-doc-id">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Share Link</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">Dokumen: <strong id="share-doc-title"></strong></p>
                        <div id="share-result-container"></div>
                        <div class="mb-3">
                            <label class="form-label">Password (Opsional)</label>
                            <input type="password" name="share_password" id="share_password" class="form-control" placeholder="Kosongkan jika tidak butuh password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kadaluarsa (Opsional)</label>
                            <input type="datetime-local" name="share_expires_at" id="share_expires_at" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary" id="btn-create-share">Buat Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Replace Document Modal -->
    <div class="modal fade" id="replaceDocumentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="replaceForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="replace_document_file">
                    <input type="hidden" name="doc_id" id="replace-doc-id">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Document File</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih File PDF Baru</label>
                            <input type="file" name="new_pdf_file" class="form-control" accept=".pdf" required>
                            <small class="text-info">File lama akan disimpan sebagai versi riwayat.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan Perubahan</label>
                            <textarea name="version_notes" class="form-control" rows="3" placeholder="Apa yang berubah di versi ini?"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Versi Baru</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS Dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js" defer></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="../assets/noprintjs/noprint.js"></script>
    <script>
        $(document).ready(function () {
            var table = $('#datatable').DataTable({
                "dom": 'lrtip', // Hide default search box
                "lengthChange": false,
                "info": false,
                pageLength: 10,
                scrollX: true,
                language: {
                    paginate:{
                        next: "›",
                        previous: "‹"
                    }
                }
            });

            // Connect custom search input to DataTable
            $('#customSearch').on('keyup', function() {
                table.search(this.value).draw();
                
            });
            $('#customKolom').on('keyup', function() {
                table.columns.adjust().draw();
            });

            // Initialize Select2 for modals
            $('#upload_departements_access, #upload_users_access').select2({
                placeholder: "-- Pilih Akses --",
                allowClear: true,
                dropdownParent: $('#UploadFileModal')
            });
            // Re-init for edit modal separately because dropdownParent matters
            $('#EditFileModal').on('shown.bs.modal', function() {
                $('#edit_departements_access, #edit_users_access', this).select2({
                    placeholder: "-- Pilih Akses --",
                    allowClear: true,
                    dropdownParent: $('#EditFileModal')
                });
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // Reopen CreateFolderModal if filters are active
            <?php if (!empty($f_dept) || !empty($f_tahun)): ?>
                var folderModal = new bootstrap.Modal(document.getElementById('CreateFolderModal'));
                folderModal.show();
            <?php endif; ?>
        });
    </script>

    <script>
        $(document).ready(function () {
            // Auto-fill folder name from department
            $('#folder_dept_select').on('change', function () {
                var selectedOption = $(this).find('option:selected');
                var deptName = selectedOption.data('name');
                if (deptName) {
                    $('#nama_dep').val(deptName);
                }
            });
        });
        // Sidebar Inspector Logic
        const sidebarPreviewIframe = document.getElementById('sidebarPreviewIframe');
        const previewEmpty = document.getElementById('preview-empty');
        const inspectorTitle = document.getElementById('inspector-title');
        const inspectorSubtitle = document.getElementById('inspector-subtitle');
        const inspectorList = document.getElementById('inspector-list');

        function updateInspector(data) {
            // Update Title & Subtitle in Detail Tab (reusing existing elements or creating new structure)
            const detailContent = document.getElementById('detail-content');
            
                <?php
                // Logic for Revision Status
                // If updatedAt is empty, null, or '-', it means no revision.
                // JS logic:
                /* 
                   const updated = (data.updatedAt && data.updatedAt !== '-' && data.updatedAt !== '') ? true : false;
                   const revisionStatus = updated ? `<span class="text-primary fw-bold">Sudah</span> (${data.updatedAt})` : `<span class="text-success fw-bold">Belum</span>`;
                */
                ?>
                const updated = (data.updatedAt !== '' && data.updatedAt !== '-') ? true : false;
                const revisionStatus = updated ? `<span class="text-primary fw-bold">Sudah</span> (${data.updatedAt})` : `<span class="text-success fw-bold">Belum</span>`;

                /* ... inside innerHTML ... 
                   | Revisi : ${revisionStatus}
                */
                
            detailContent.innerHTML = `
                <div class="px-1">
                <table style="width: 100%;">
                <tr>
                <td><p style="font-weight: bold; font-size: 15px;margin-top: 2px;margin-bottom: 0px;">Nama File : ${data.fileName}</p></td>
                </tr>
                <tr><td colspan="2"><p style="font-weight: bold; font-size: 12px;margin-top: 1px;margin-bottom: 0px;">Kode File : ${data.fileCode} | Departemen : ${data.dept} | ${data.size} </p></td></tr>
                <tr><td colspan="2"><p style="font-size: 12px;margin-top: 2px;margin-bottom: 0px;">| Tahun : ${data.tahun} | Tanggal Upload : ${data.uploadedAt} <s>| Revisi : ${revisionStatus}</s> Perbaikan !</p></td></tr>
                </table>
                </div>
            `;

            // Update Preview Sidebar
            const viewerPath = '../assets/pdfjs/web/viewer.html';
            const filePath = `../../../uploads/${encodeURIComponent(data.tahun)}/${encodeURIComponent(data.dept)}/${encodeURIComponent(data.fileName)}`;
            
            sidebarPreviewIframe.src = `${viewerPath}?file=${filePath}`;
            sidebarPreviewIframe.style.display = 'block';
            if(previewEmpty) previewEmpty.style.display = 'none';
        }

        // Handle row click for inspection
        // Note: DataTable redraws the table on pagination/search, so we need delegated event listeners
        $('#datatable tbody').on('click', 'tr', function () {
            const tr = $(this);
            
             // Ignore clicks on empty rows or loading rows
            if (!tr.data('file')) return;

            // Highlight Logic
            $('#datatable tbody tr').removeClass('table-primary');
            tr.addClass('table-primary');

            const data = {
                fileName: tr.data('file'),
                fileCode: tr.data('code'),
                tahun: tr.data('tahun'),
                dept: tr.data('dept'),
                uploadedAt: tr.data('uploaded'),
                updatedAt: tr.data('updated'),
                size: tr.data('size'),
                type: tr.data('type'),
                icon: tr.data('icon'),
                color: tr.data('color'),
                id: tr.data('id'),
                deptId: tr.data('dept-id'),
                tahunId: tr.data('tahun-id')
            };
            
            updateInspector(data);
        });

        const safeModalInit = (id) => {
            const el = document.getElementById(id);
            return el ? new bootstrap.Modal(el) : null;
        };

        const uploadModal = safeModalInit('uploadModal');
        // Only add listener if button and modal exist
        const uploadBtn = document.querySelector('.btn-primary');
        const createFolderModal = safeModalInit('createFolderModal');
        const createFolderBtn = document.querySelector('.btn-secondary'); // Selector ini general, hati-hati
        const pdfModal = safeModalInit('pdfModal');
        const pdfIframe = document.getElementById('pdfIframe');
        
        // Defined globally for onclick access
        window.openPdf = (fileName, tahun, namaDept) => {
             const viewerPath = '../assets/pdfjs/web/viewer.html';
             const filePath = `../../../uploads/${encodeURIComponent(tahun)}/${encodeURIComponent(namaDept)}/${encodeURIComponent(fileName)}`;
             if(pdfIframe) {
                 pdfIframe.src = `${viewerPath}?file=${filePath}`;
                 if(pdfModal) pdfModal.show();
             }
        };

        if (pdfModal && pdfIframe) {
            // Function to open PDF
            const openPdf = (fileName, tahun, namaDept) => {
                // Path to viewer.html relative to this file (admin/manage_documents.php) -> ../assets/pdfjs/web/viewer.html
                // Path to file relative to viewer.html (assets/pdfjs/web/viewer.html) -> ../../../uploads/tahun/dept/filename
                const viewerPath = '../assets/pdfjs/web/viewer.html';
                const filePath = `../../../uploads/${encodeURIComponent(tahun)}/${encodeURIComponent(namaDept)}/${encodeURIComponent(fileName)}`;
                pdfIframe.src = `${viewerPath}?file=${filePath}`;
                pdfModal.show();
            };

            // Handle dropdown "Lihat" button
            document.querySelectorAll('.view-pdf-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const fileName = btn.getAttribute('data-file');
                    const tahun = btn.getAttribute('data-tahun');
                    const namaDept = btn.getAttribute('data-dept');
                    if (fileName) openPdf(fileName, tahun, namaDept);
                });
            });

            // Optional: Also open when clicking the file name in the table
            document.querySelectorAll('.file-name').forEach(el => {
                el.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const row = el.closest('tr');
                    const viewBtn = row.querySelector('.view-pdf-btn');
                    if (viewBtn) {
                        const actualFileName = viewBtn.getAttribute('data-file');
                        const tahun = viewBtn.getAttribute('data-tahun');
                        const namaDept = viewBtn.getAttribute('data-dept');
                        openPdf(actualFileName, tahun, namaDept);
                    }
                });
            });

            // Clear src when modal is hidden
            document.getElementById('pdfModal').addEventListener('hidden.bs.modal', () => {
                pdfIframe.src = '';
            });
        }

        // Edit Document Modal Populator
        const editModal = new bootstrap.Modal(document.getElementById('EditFileModal'));
        $(document).on('click', '.edit-doc-btn', function (e) {
            e.preventDefault();
            const btn = $(this);
            const id = btn.attr('data-id');
            const code = btn.attr('data-code');
            const name = btn.attr('data-name');
            const deptId = btn.attr('data-dept-id');
            const tahunId = btn.attr('data-tahun-id');

            // Strip extension for display if needed (update_documents.php handles it anyway)
            // const nameWithoutExt = name.replace(/\.[^/.]+$/, ""); // Let user edit full name or not? Assuming logical
            const nameWithoutExt = name.replace(/\.[^/.]+$/, "");

            $('#edit_doc_id').val(id);
            $('#edit_file_code').val(code);
            $('#edit_file_name').val(nameWithoutExt);
            $('#edit_department_id').val(deptId);
            $('#edit_tahun_id').val(tahunId);

            // Fetch Access Details via AJAX (handler added to top of file)
            $.getJSON(`manage_documentsrevbaru.php?action=get_details&type=document&id=${id}`, function(data) {
                 // Note: We need to ensure Select2 is initialized for these if we want rich UI
                 // For now, standard select update
                 $('#edit_departements_access').val(data.departements).trigger('change');
                 $('#edit_users_access').val(data.users).trigger('change');
                 editModal.show();
            });
        });

        // Delete Document Function
        $(document).on('click', '.delete-doc-btn', function (e) {
            e.preventDefault();
            const btn = $(this);
            const docId = btn.attr('data-doc-id');
            const fileName = btn.attr('data-file-name');
            const fileCode = btn.attr('data-file-code');

            if (confirm(`Apakah Anda yakin ingin menghapus dokumen "${fileCode}"?\n\nFile: ${fileName}\n\nTindakan ini tidak dapat dibatalkan!`)) {
                // Create form and submit
                const form = $('<form method="POST" action="hapus_doc.php">' +
                    '<input type="hidden" name="action" value="hapus_doc">' +
                    '<input type="hidden" name="id" value="' + docId + '">' +
                    '</form>');
                $('body').append(form);
                form.submit();
            }
        });
        // Delete Tahun Function
        document.querySelectorAll('.delete-tahun-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const tahunId = btn.getAttribute('data-tahun-id');
                const tahunVal = btn.getAttribute('data-tahun');

                if (confirm(`Apakah Anda yakin ingin menghapus tahun "${tahunVal}"?\n\nSemua folder dan dokumen di dalam tahun ini akan ikut terhapus!\n\nTindakan ini tidak dapat dibatalkan!`)) {
                    // Create form and submit to hapus_tahun.php
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'hapus_tahun.php';

                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'hapus_tahun';

                    const tahunIdInput = document.createElement('input');
                    tahunIdInput.type = 'hidden';
                    tahunIdInput.name = 'tahun_id';
                    tahunIdInput.value = tahunId;

                    form.appendChild(actionInput);
                    form.appendChild(tahunIdInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
        // Delete Folder Function
        document.querySelectorAll('.delete-folder-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const folderId = btn.getAttribute('data-folder-id');
                const folderVal = btn.getAttribute('data-folder');

                if (confirm(`Apakah Anda yakin ingin menghapus folder "${folderVal}"?\n\nSemua folder dan dokumen di dalam folder ini akan ikut terhapus!\n\nTindakan ini tidak dapat dibatalkan!`)) {
                    // Create form and submit to hapus_tahun.php
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'hapus_folder.php';

                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'hapus_folder';

                    const folderIdInput = document.createElement('input');
                    folderIdInput.type = 'hidden';
                    folderIdInput.name = 'id_fold';
                    folderIdInput.value = folderId;

                    form.appendChild(actionInput);
                    form.appendChild(folderIdInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Print Function
        const printBtn = document.getElementById('printBtn');
        if (printBtn) {
            printBtn.addEventListener('click', () => {
                const fileName = document.getElementById('previewFileName').textContent;
                const fileCode = document.getElementById('previewFileCode').textContent;
                const printContent = `
                    <div style="font-family: Arial; padding: 20px;">
                        <h2 style="color: #0d6efd;">📄 Document Preview</h2>
                        <hr>
                        <p><strong>File Name:</strong> ${fileName}</p>
                        <p><strong>File Code:</strong> ${fileCode}</p>
                        <p><strong>Generated On:</strong> ${new Date().toLocaleString()}</p>
                    </div>
                `;
                const printWindow = window.open('', '_blank');
                printWindow.document.write(printContent);
                printWindow.document.close();
                printWindow.print();
            });
        }

        /* CONTEXT MENU IMPLEMENTATION */
        $(document).ready(function() {
            let contextMenu = null;

            $(document).on('click', function () { if (contextMenu) $(contextMenu).remove(); });

            // Right click on Table Rows (Documents)
            $(document).on('contextmenu', '.file-row', function (event) {
                event.preventDefault();
                event.stopPropagation();
                if (contextMenu) $(contextMenu).remove();

                const id = $(this).data('id');
                const title = $(this).data('file');
                const code = $(this).data('code');
                const deptId = $(this).data('dept-id');
                const tahunId = $(this).data('tahun-id');

                let menuHtml = '<ul class="dropdown-menu d-block">';
                menuHtml += `<li><a class="dropdown-item" href="#" data-action="edit-document" data-id="${id}" data-file="${title}" data-code="${code}" data-dept-id="${deptId}" data-tahun-id="${tahunId}"><i class="fas fa-edit me-2"></i>Edit Details & Access</a></li>`;
                menuHtml += `<li><a class="dropdown-item" href="#" data-action="replace-document" data-id="${id}"><i class="fas fa-file-import me-2"></i>Update File</a></li>`;
                menuHtml += `<li><a class="dropdown-item" href="#" data-action="view-versions" data-id="${id}" data-title="${title}"><i class="fas fa-history me-2"></i>Version History</a></li>`;
                menuHtml += `<li><a class="dropdown-item" href="#" data-action="share-document" data-id="${id}" data-title="${title}"><i class="fas fa-share-alt me-2"></i>Create Share Link</a></li>`;
                menuHtml += `<li><hr class="dropdown-divider"></li>`;
                menuHtml += `<li><a class="dropdown-item text-danger" href="manage_documentsrevbaru.php?action=delete_document&id=${id}" onclick="return confirm('Yakin hapus dokumen ini?');"><i class="fas fa-trash me-2"></i>Delete</a></li>`;
                menuHtml += '</ul>';

                renderContextMenu(event, menuHtml);
            });

            // Right click on Sidebar Folders
            $(document).on('contextmenu', '.folder-item', function (event) {
                // For now, only handle departments if needed. 
                // In manage_documents.php they have nested folders, here it's mostly dept list.
                // Let's add basic edit if it's not "Semua Departemen"
                const href = $(this).attr('href');
                if (href === '?nama_dept=') return; // Skip "Semua Departemen"

                event.preventDefault();
                event.stopPropagation();
                if (contextMenu) $(contextMenu).remove();

                const name = $(this).find('.folder-title').text();
                // We need the ID of the department. Since it's not in data-id, we can skip or look it up.
                // For now, let's focus on Documents as requested by line 754-859 reference.
            });

            function renderContextMenu(event, html) {
                contextMenu = document.createElement('div');
                contextMenu.className = 'context-menu';
                contextMenu.innerHTML = html;
                document.body.appendChild(contextMenu);
                
                // Position check to keep inside window
                let top = event.pageY;
                let left = event.pageX;
                if (top + $(contextMenu).height() > $(window).height() + $(window).scrollTop()) {
                    top -= $(contextMenu).height();
                }
                if (left + $(contextMenu).width() > $(window).width()) {
                    left -= $(contextMenu).width();
                }

                $(contextMenu).css({ top: top, left: left });
            }

            // Context Menu Action Handlers
            $(document).on('click', '.context-menu a', function (e) {
                e.preventDefault();
                const action = $(this).data('action');
                const id = $(this).data('id');
                const title = $(this).data('title');

                if (action === 'edit-document') {
                    // Populate Edit Modal (already exists in revbaru)
                    $('#edit_doc_id').val(id);
                    $('#edit_file_code').val($(this).data('code'));
                    $('#edit_file_name').val($(this).data('file'));
                    $('#edit_department_id').val($(this).data('dept-id'));
                    $('#edit_tahun_id').val($(this).data('tahun-id'));
                    
                    // Fetch Access Details via AJAX (handler added to top of file)
                    $.getJSON(`manage_documentsrevbaru.php?action=get_details&type=document&id=${id}`, function(data) {
                         // Note: We need to ensure Select2 is initialized for these if we want rich UI
                         // For now, standard select update
                         $('#edit_departements_access').val(data.departements).trigger('change');
                         $('#edit_users_access').val(data.users).trigger('change');
                         new bootstrap.Modal(document.getElementById('EditFileModal')).show();
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
                    $.getJSON(`manage_documentsrevbaru.php?action=get_versions&id=${id}`, function (versions) {
                        tbody.empty();
                        if (versions.length > 0) {
                            versions.forEach(v => {
                                const notes = v.notes ? v.notes.replace(/\n/g, '<br>') : '<em class="text-muted">Tidak ada catatan</em>';
                                const row = `<tr>
                                    <td>${new Date(v.uploaded_at).toLocaleString('id-ID')}</td>
                                    <td>${v.username}</td>
                                    <td>${notes}</td>
                                    <td>${v.version_file_name}</td>
                                    <td><a href="manage_documentsrevbaru.php?action=restore_version&doc_id=${id}&version_id=${v.id}" class="btn btn-sm btn-outline-primary" onclick="return confirm('Yakin memulihkan versi ini?');">Pulihkan</a></td>
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
                    $('#share-result-container').empty();
                    new bootstrap.Modal(document.getElementById('shareDocumentModal')).show();
                }
            });

            // AJAX Share Form Submission
            $('#shareForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'manage_documentsrevbaru.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(resp) {
                        if (resp.status === 'success') {
                            $('#share-result-container').html(`<div class="alert alert-success mt-2">Link Berhasil Dibuat:<br><input type="text" class="form-control mt-2" value="${resp.link}" readonly onclick="this.select()"></div>`);
                        } else {
                            alert('Gagal membuat share link: ' + resp.message);
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>