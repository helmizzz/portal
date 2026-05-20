<?php
require_once '../includes/theme_handler.php'; // Mengikat Talisman Inti

// --- FUNGSI EKSPOR PENGGUNA KE CSV ---
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    require_once '../includes/db_connect.php';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=users_template_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    // Header untuk template/ekspor
    fputcsv($output, ['username', 'password', 'role_id', 'departement_id']);

    // Jika ingin mengekspor data pengguna yang ada (tanpa password)
    $sql = "SELECT username, '' as password, role_id, departement_id FROM users ORDER BY username ASC";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }

    fclose($output);
    $conn->close();
    exit();
}


// --- LOGIKA FLASH MESSAGE & AUTENTIKASI ---
$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$admin_id = $_SESSION['user_id'];

// --- LOGIKA AKSI (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $action_success = false;
    $success_message = '';
    $error_message = '';

    // --- LOGIKA IMPOR PENGGUNA DARI CSV ---
    if ($action === 'import_csv') {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['csv_file'];
            $file_type = mime_content_type($file['tmp_name']);

            if ($file_type === 'text/csv' || $file_type === 'text/plain' || $file_type === 'application/vnd.ms-excel') {
                $conn->begin_transaction();
                try {
                    $handle = fopen($file['tmp_name'], "r");
                    $header = fgetcsv($handle, 1000, ","); // Baca header

                    $added_count = 0;
                    $updated_count = 0;

                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $username = trim($data[0]);
                        $password = trim($data[1]);
                        $role_id = (int) $data[2];
                        $departement_id = empty($data[3]) ? NULL : (int) $data[3];

                        if (empty($username) || empty($role_id))
                            continue;

                        // Cek apakah user sudah ada
                        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
                        $stmt_check->bind_param("s", $username);
                        $stmt_check->execute();
                        $result_check = $stmt_check->get_result();
                        $existing_user = $result_check->fetch_assoc();

                        if ($existing_user) {
                            // Update user yang sudah ada
                            $user_id_to_update = $existing_user['id'];
                            $sql_update = "UPDATE users SET role_id = ?, departement_id = ? WHERE id = ?";
                            $params_update = [$role_id, $departement_id, $user_id_to_update];
                            $types_update = "iii";

                            if (!empty($password)) {
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $sql_update = "UPDATE users SET password = ?, role_id = ?, departement_id = ? WHERE id = ?";
                                $params_update = [$hashed_password, $role_id, $departement_id, $user_id_to_update];
                                $types_update = "siii";
                            }

                            $stmt_update = $conn->prepare($sql_update);
                            $stmt_update->bind_param($types_update, ...$params_update);
                            $stmt_update->execute();
                            $updated_count++;
                        } else {
                            // Tambah user baru
                            if (empty($password))
                                continue; // Password wajib untuk user baru
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt_insert = $conn->prepare("INSERT INTO users (username, password, role_id, departement_id) VALUES (?, ?, ?, ?)");
                            $stmt_insert->bind_param("ssii", $username, $hashed_password, $role_id, $departement_id);
                            $stmt_insert->execute();
                            $added_count++;
                        }
                    }
                    fclose($handle);
                    $conn->commit();
                    $action_success = true;
                    $success_message = "<div class='alert alert-success'>Impor berhasil! Ditambahkan: $added_count pengguna, Diperbarui: $updated_count pengguna.</div>";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error_message = "<div class='alert alert-danger'>Terjadi kesalahan saat impor: " . $e->getMessage() . "</div>";
                }
            } else {
                $error_message = "<div class='alert alert-danger'>Format file tidak valid. Harap unggah file CSV.</div>";
            }
        } else {
            $error_message = "<div class='alert alert-danger'>Gagal mengunggah file atau tidak ada file yang dipilih.</div>";
        }
    }
    // --- AKSI LAINNYA ---
    elseif ($action === 'reset_password') {
        $user_id_to_reset = (int) $_POST['user_id_reset'];
        $new_password = $_POST['new_password'];
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql_reset = "UPDATE users SET password = ? WHERE id = ?";
            $stmt_reset = $conn->prepare($sql_reset);
            $stmt_reset->bind_param("si", $hashed_password, $user_id_to_reset);
            if ($stmt_reset->execute()) {
                $action_success = true;
                $success_message = "<div class='alert alert-success'>Password berhasil direset.</div>";
            }
        }
    } elseif ($action === 'add_user') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        if (!empty($username) && !empty($password)) {
            $departement_id = $_POST['departement_id'] === '' ? NULL : (int) $_POST['departement_id'];
            $role_id = (int) $_POST['role_id'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, role_id, departement_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $username, $hashed_password, $role_id, $departement_id);
            if ($stmt->execute()) {
                $action_success = true;
                $success_message = "<div class='alert alert-success'>User berhasil ditambahkan.</div>";
            } else {
                $error_message = "<div class='alert alert-danger'>Gagal menambahkan. Username mungkin sudah ada.</div>";
            }
        }
    } elseif ($action === 'edit_user') {
        $user_id_edit = (int) $_POST['user_id'];
        $new_username = trim($_POST['new_username']);
        if (!empty($new_username)) {
            $new_departement_id = $_POST['new_departement_id'] === '' ? NULL : (int) $_POST['new_departement_id'];
            $new_role_id = (int) $_POST['new_role_id'];
            $sql = "UPDATE users SET username = ?, departement_id = ?, role_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siii", $new_username, $new_departement_id, $new_role_id, $user_id_edit);
            if ($stmt->execute()) {
                $action_success = true;
                $success_message = "<div class='alert alert-success'>User berhasil diupdate.</div>";
            }
        }
    }

    if ($action_success) {
        $_SESSION['flash_message'] = $success_message;
    } else {
        $_SESSION['flash_message'] = $error_message ?: "<div class='alert alert-danger'>Terjadi kesalahan.</div>";
    }
    header("Location: manage_users.php");
    exit();
}

// --- LOGIKA AKSI (GET) ---
if (isset($_GET['action'])) {
    $action_success = false;
    $success_message = '';
    if ($_GET['action'] === 'toggle_status' && isset($_GET['id'])) {
        $user_id_to_toggle = (int) $_GET['id'];
        if ($user_id_to_toggle !== $admin_id) {
            $sql_toggle = "UPDATE users SET is_active = NOT is_active WHERE id = ?";
            $stmt_toggle = $conn->prepare($sql_toggle);
            $stmt_toggle->bind_param("i", $user_id_to_toggle);
            if ($stmt_toggle->execute()) {
                $action_success = true;
                $success_message = "<div class='alert alert-success'>Status pengguna berhasil diubah.</div>";
            }
        }
    } elseif ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id_to_delete = (int) $_GET['id'];
        if ($id_to_delete !== $admin_id) {
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_to_delete);
            if ($stmt->execute()) {
                $action_success = true;
                $success_message = "<div class='alert alert-success'>User berhasil dihapus.</div>";
            }
        }
    }

    if ($action_success) {
        $_SESSION['flash_message'] = $success_message;
        header("Location: manage_users.php");
        exit();
    }
}

// --- PENGAMBILAN DATA UNTUK TABEL DAN DROPDOWN ---
$search_query = isset($_GET['q']) ? $_GET['q'] : '';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$users = [];
$total_users = 0;
$total_pages = 1;
$search_clause = $search_query ? "WHERE u.username LIKE ?" : "";
$sql_count = "SELECT COUNT(*) AS total FROM users u $search_clause";
$stmt_count = $conn->prepare($sql_count);
if ($search_query) {
    $search_param = "%" . $search_query . "%";
    $stmt_count->bind_param("s", $search_param);
}
$stmt_count->execute();
$total_users = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);
$sql = "SELECT u.id, u.username, r.role_name, d.name AS departement_name, u.departement_id, u.role_id, u.is_active FROM users u LEFT JOIN departements d ON u.departement_id = d.id LEFT JOIN roles r ON u.role_id = r.id $search_clause ORDER BY u.id ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($search_query) {
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("sii", $search_param, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$departements = [];
$sql_dept = "SELECT id, name FROM departements";
$result_dept = $conn->query($sql_dept);
while ($row = $result_dept->fetch_assoc()) {
    $departements[] = $row;
}
$roles = [];
$sql_roles = "SELECT id, role_name FROM roles ORDER BY role_name ASC";
$result_roles = $conn->query($sql_roles);
while ($row = $result_roles->fetch_assoc()) {
    $roles[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Manajemen User</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/theme.js" defer></script>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <h2>User & Employee Management</h2>
        <?= $message ?>
        <div class="card mb-4">
            <div class="card-header">Add New User / Employee</div>
            <div class="card-body">
                <form action="manage_users.php" method="POST">
                    <input type="hidden" name="action" value="add_user">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="username" class="form-label">Username</label><input
                                type="text" name="username" id="username" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label for="password" class="form-label">Password</label><input
                                type="password" name="password" id="password" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label for="departement_id"
                                class="form-label">Department</label><select name="departement_id" id="departement_id"
                                class="form-select">
                                <option value="">There isn't any</option><?php foreach ($departements as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                        <div class="col-md-6 mb-3"><label for="role_id" class="form-label">Role /
                                Position</label><select name="role_id" id="role_id"
                                class="form-select"><?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">User & Employee List</h5>
                <div class="d-flex align-items-center">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                            data-bs-target="#importUserModal"><i class="fas fa-file-import"></i> Impor</button>
                        <a href="manage_users.php?action=export_csv" class="btn btn-info btn-sm"><i
                                class="fas fa-file-export"></i> Ekspor</a>
                    </div>
                    <form class="d-flex" action="manage_users.php" method="GET">
                        <input class="form-control me-2 form-control-sm" type="search" placeholder="Cari user..."
                            name="q" value="<?= htmlspecialchars($search_query) ?>">
                        <button class="btn btn-outline-primary btn-sm" type="submit">Cari</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Department</th>
                                <th>Role / Position</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0):
                                foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['departement_name'] ?: 'N/A') ?></td>
                                        <td><?= htmlspecialchars($user['role_name']) ?></td>
                                        <td><?php if ($user['is_active']): ?><span
                                                    class="badge bg-success">Active</span><?php else: ?><span
                                                    class="badge bg-danger">Non active</span><?php endif; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#editUserModal" data-id="<?= $user['id'] ?>"
                                                    data-username="<?= htmlspecialchars($user['username']) ?>"
                                                    data-departement-id="<?= $user['departement_id'] ?>"
                                                    data-role-id="<?= $user['role_id'] ?>">Edit</button>
                                                <button class="btn btn-info btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#resetPasswordModal" data-id="<?= $user['id'] ?>"
                                                    data-username="<?= htmlspecialchars($user['username']) ?>">Reset
                                                    Pass</button>
                                                <?php if ($user['is_active']): ?><a
                                                        href="manage_users.php?action=toggle_status&id=<?= $user['id'] ?>"
                                                        class="btn btn-secondary btn-sm"
                                                        onclick="return confirm('Yakin menonaktifkan user ini?');">Nonaktifkan</a><?php else: ?><a
                                                        href="manage_users.php?action=toggle_status&id=<?= $user['id'] ?>"
                                                        class="btn btn-success btn-sm"
                                                        onclick="return confirm('Yakin mengaktifkan user ini?');">Activate</a><?php endif; ?>
                                                <a href="manage_users.php?action=delete&id=<?= $user['id'] ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Yakin menghapus user ini?');">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <nav>
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>"><a class="page-link"
                                href="?page=<?= $page - 1 ?>&q=<?= urlencode($search_query) ?>">Previous</a></li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>"><a class="page-link"
                                    href="?page=<?= $i ?>&q=<?= urlencode($search_query) ?>"><?= $i ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>"><a class="page-link"
                                href="?page=<?= $page + 1 ?>&q=<?= urlencode($search_query) ?>">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="manage_users.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Modify User</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_user"><input type="hidden" name="user_id"
                            id="edit-user-id">
                        <div class="mb-3"><label for="edit-username" class="form-label">Username</label><input
                                type="text" name="new_username" id="edit-username" class="form-control" required></div>
                        <div class="mb-3"><label for="edit-departement" class="form-label">Department</label><select
                                name="new_departement_id" id="edit-departement" class="form-select">
                                <option value="">There isn't any</option><?php foreach ($departements as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                        <div class="mb-3"><label for="edit-role" class="form-label">Role / Position</label><select
                                name="new_role_id" id="edit-role"
                                class="form-select"><?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                                <?php endforeach; ?>
                            </select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Save</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="manage_users.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Reset Password</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reset_password"><input type="hidden"
                            name="user_id_reset" id="reset-user-id">
                        <p>You will reset the password for the user: <strong id="reset-username-display"></strong></p>
                        <div class="mb-3"><label for="new_password" class="form-label">Enter New Password</label><input
                                type="password" name="new_password" id="new_password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Reset
                            Password</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="manage_users.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Users from CSV</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="import_csv">
                        <div class="alert alert-info">
                            <strong>Petunjuk Format CSV:</strong>
                            <ul>
                                <li>The file must be in CSV format with the following headers:
                                    <code>username,password,role_id,departement_id</code>
                                </li>
                                <li><strong>username:</strong> Required. If the username already exists, the data will
                                    be updated. If it doesn't exist, a new user will be created.</li>
                                <li><strong>password:</strong> Required for new users. For existing users, fill this
                                    field only if you want to reset the password; leave it blank otherwise.</li>
                                <li><strong>role_id:</strong> Required. This is the ID of the role (e.g., 1 for Admin, 2
                                    for User).</li>
                                <li><strong>departement_id:</strong> Optional. This is the ID of the department. Leave
                                    it blank if not applicable.</li>
                            </ul>
                            <a href="manage_users.php?action=export_csv">Download CSV Template (contains current user
                                data)</a>
                        </div>
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Select CSV File</label>
                            <input class="form-control" type="file" name="csv_file" id="csv_file" accept=".csv"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Import Users</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editUserModal = document.getElementById('editUserModal');
            if (editUserModal) {
                editUserModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    this.querySelector('#edit-user-id').value = button.dataset.id;
                    this.querySelector('#edit-username').value = button.dataset.username;
                    this.querySelector('#edit-departement').value = button.dataset.departementId;
                    this.querySelector('#edit-role').value = button.dataset.roleId;
                });
            }
            const resetPasswordModal = document.getElementById('resetPasswordModal');
            if (resetPasswordModal) {
                resetPasswordModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    this.querySelector('#reset-user-id').value = button.dataset.id;
                    this.querySelector('#reset-username-display').textContent = button.dataset.username;
                });
            }
        });
    </script>
</body>

</html>