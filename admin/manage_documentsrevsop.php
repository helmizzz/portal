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
if (!in_array('manage_documentsrevsop', $user_permissions) && $_SESSION['role'] !== 'Admin') {
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

// Auto filter and lock based on user's department for all non-admin staff
$is_restricted = false;
if ($_SESSION['role'] !== 'Admin' && !empty($_SESSION['departement_name'])) {
    $dept_filter = $_SESSION['departement_name'];
    $is_restricted = true;
}

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
        LEFT JOIN tahun t ON a.tahun = t.tahun";
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
    $sql .= " WHERE ";
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
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>


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

        .table-responsive {
            min-height: 400px;
            /* Memastikan ada ruang untuk dropdown aksi */
        }
    </style>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container-fluid mt-4">
        <div
            class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center mb-3 justify-content-between gap-3">
            <p style="font-size:auto;">📁 Document List :
                <?php
                $tahun_filter = $_GET['tahun'] ?? '';
                $dept_filter = $_GET['nama_dept'] ?? '';

                if (empty($dept_filter)) {
                    echo "Semua Departemen";
                } else {
                    $stmt = $conn->prepare("SELECT name FROM departements WHERE name = ?");
                    $stmt->bind_param("s", $dept_filter);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $row = $res->fetch_assoc();
                    echo htmlspecialchars($row['name'] ?? '-');
                }
                ?>
                :
                <?php
                if (empty($tahun_filter)) {
                    echo "Semua Tahun";
                } else {
                    $stmt = $conn->prepare("SELECT tahun FROM documents WHERE tahun = ?");
                    $stmt->bind_param("i", $tahun_filter);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $row = $res->fetch_assoc();
                    echo htmlspecialchars($row['tahun'] ?? '-');
                }
                ?>
            </p>
        </div>
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <select name="tahun" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Tahun --</option>
                    <?php while ($row = mysqli_fetch_assoc($tahunList)): ?>
                        <option value="<?= htmlspecialchars($row['tahun']) ?>" <?= ($row['tahun'] == $tahun_filter) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['tahun']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="nama_dept" class="form-select" onchange="this.form.submit()" <?= $is_restricted ? 'disabled' : '' ?>>
                    <option value="">-- Semua Departemen --</option>
                    <?php while ($row = mysqli_fetch_assoc($deptList)): ?>
                        <option value="<?= htmlspecialchars($row['nama_dept']) ?>" <?= ($row['nama_dept'] == $dept_filter) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['nama_dept']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <?php if ($is_restricted): ?>
                    <input type="hidden" name="nama_dept" value="<?= htmlspecialchars($dept_filter) ?>">
                <?php endif; ?>
            </div>
        </form>
        <div class="table-responsive">
            <table id="datatable" style="width: 100%;font-size: 12px;" class="table table-striped table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>Kode File</th>
                        <th>Nama File</th>
                        <th>Departemen</th>
                        <th>Dok. Tahun</th>
                        <th>Tanggal Upload</th>
                        <th>Last Modified</th>
                        <th>Uploader</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['file_code']) ?></td>
                            <td><?= htmlspecialchars($row['file_name']) ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($row['nama_dept']) ?></span></td>
                            <td><?= htmlspecialchars($row['tahun']) ?></td>
                            <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
                            <td><?= !empty($row['updated_at']) ? htmlspecialchars($row['updated_at']) : '-' ?></td>
                            <td><?= htmlspecialchars($row['created_by']) ?></td>
                            <td><?php
                            echo "<div class='dropdown'>";
                            echo "<button class='btn btn-sm btn-light' data-bs-toggle='dropdown' data-bs-boundary='viewport'><i class='bi bi-three-dots-vertical'></i></button>";
                            echo "<ul class='dropdown-menu dropdown-menu-end'>";
                            echo "<li><a class='dropdown-item view-pdf-btn' href='#' data-file='" . htmlspecialchars($row['file_name']) . "' data-tahun='" . htmlspecialchars($row['tahun']) . "' data-dept='" . htmlspecialchars($row['nama_dept']) . "'><i class='bi bi-eye'></i> Lihat</a></li>";
                            echo "<li><a class='dropdown-item edit-doc-btn' href='#' 
                                    data-id='" . $row['id'] . "' 
                                    data-code='" . htmlspecialchars($row['file_code']) . "' 
                                    data-name='" . htmlspecialchars($row['file_name']) . "' 
                                    data-dept-id='" . $row['dept_id'] . "' 
                                    data-tahun-id='" . $row['tahun_id'] . "'>
                                    <i class='bi bi-pencil'></i> Edit</a></li>";
                            // echo "<li><a class='dropdown-item' ><i class='bi bi-printer'></i> Print</a></li>";
                            echo "<li><hr class='dropdown-divider'></li>";
                            echo "<li><a class='dropdown-item text-danger delete-doc-btn' href='#' data-doc-id='" . $row['id'] . "' data-file-name='" . htmlspecialchars($row['file_name']) . "' data-file-code='" . htmlspecialchars($row['file_code']) . "'><i class='bi bi-trash'></i> Delete</a></li>";
                            echo "</ul>";
                            echo "</div>";
                            ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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
                            <small class="text-muted">Pilih departemen untuk menggunakan nama departemen sebagai nama
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
                    <h5 class="modal-title" id="EditFileModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Dokumen
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

    <!-- JS Dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/theme.js" defer></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#datatable').DataTable({
                pageLength: 10,
                scrollX: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    paginate: {
                        next: "›",
                        previous: "‹"
                    }
                }
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
                    const fileName = btn.getAttribute('data-file');
                    const tahun = btn.getAttribute('data-tahun');
                    const namaDept = btn.getAttribute('data-dept');
                    if (fileName) openPdf(fileName, tahun, namaDept);
                });
            });

            // Optional: Also open when clicking the file name in the table
            document.querySelectorAll('.file-name').forEach(el => {
                el.addEventListener('click', () => {
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
        document.querySelectorAll('.edit-doc-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const id = btn.getAttribute('data-id');
                const code = btn.getAttribute('data-code');
                const name = btn.getAttribute('data-name');
                const deptId = btn.getAttribute('data-dept-id');
                const tahunId = btn.getAttribute('data-tahun-id');

                // Strip extension for display if needed (update_documents.php handles it anyway)
                const nameWithoutExt = name.replace(/\.[^/.]+$/, "");

                document.getElementById('edit_doc_id').value = id;
                document.getElementById('edit_file_code').value = code;
                document.getElementById('edit_file_name').value = nameWithoutExt;
                document.getElementById('edit_department_id').value = deptId;
                document.getElementById('edit_tahun_id').value = tahunId;

                editModal.show();
            });
        });

        // Delete Document Function
        document.querySelectorAll('.delete-doc-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const docId = btn.getAttribute('data-doc-id');
                const fileName = btn.getAttribute('data-file-name');
                const fileCode = btn.getAttribute('data-file-code');

                if (confirm(`Apakah Anda yakin ingin menghapus dokumen "${fileCode}"?\n\nFile: ${fileName}\n\nTindakan ini tidak dapat dibatalkan!`)) {
                    // Create form and submit
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'hapus_doc.php';

                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'hapus_doc';

                    const docIdInput = document.createElement('input');
                    docIdInput.type = 'hidden';
                    docIdInput.name = 'id';
                    docIdInput.value = docId;

                    form.appendChild(actionInput);
                    form.appendChild(docIdInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
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
    </script>
</body>

</html>