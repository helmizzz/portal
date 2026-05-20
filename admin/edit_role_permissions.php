<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Cek hak akses
$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('manage_roles', $user_permissions)) {
    header("Location: ../dashboard.php");
    exit();
}

if (!isset($_GET['role_id'])) {
    header("Location: manage_roles.php");
    exit();
}

$role_id = (int) $_GET['role_id'];

// Logika Update Hak Akses
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_permissions'])) {
    $permission_ids = $_POST['permissions'] ?? [];

    $conn->begin_transaction();
    try {
        // Hapus semua permission lama untuk role ini
        $stmt_delete = $conn->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $stmt_delete->bind_param("i", $role_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        // Masukkan permission baru
        if (!empty($permission_ids)) {
            $stmt_insert = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($permission_ids as $pid) {
                $pid_int = (int) $pid; // Pastikan integer
                $stmt_insert->bind_param("ii", $role_id, $pid_int);
                $stmt_insert->execute();
            }
            $stmt_insert->close();
        }

        $conn->commit();
        // Redirect dengan pesan sukses (opsional, bisa ditambahkan di manage_roles jika ada flash message handler)
        header("Location: manage_roles.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='alert alert-danger'>Terjadi kesalahan saat menyimpan data: " . $e->getMessage() . "</div>";
    }
}

// Ambil data role
$role = $conn->query("SELECT * FROM roles WHERE id = $role_id")->fetch_assoc();

// Ambil semua permission yang ada
$all_permissions = [];
$result_all = $conn->query("SELECT * FROM permissions ORDER BY id");
while ($row = $result_all->fetch_assoc()) {
    $all_permissions[] = $row;
}

// Ambil permission yang dimiliki role ini
$role_permissions_ids = [];
$result_role = $conn->query("SELECT permission_id FROM role_permissions WHERE role_id = $role_id");
while ($row = $result_role->fetch_assoc()) {
    $role_permissions_ids[] = $row['permission_id'];
}
?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Modify Access Rights</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/theme.js" defer></script>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <h3>Change Access Rights for Roles: <strong><?= htmlspecialchars($role['role_name']) ?></strong></h3>
        <p><?= htmlspecialchars($role['description']) ?></p>

        <form method="POST" action="edit_role_permissions.php?role_id=<?= $role_id ?>">
            <div class="card">
                <div class="card-body">
                    <h5>Select Access Rights:</h5>
                    <div class="row">
                        <?php foreach ($all_permissions as $permission): ?>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="permissions[]"
                                            value="<?= $permission['id'] ?>" id="perm_<?= $permission['id'] ?>"
                                            <?= in_array($permission['id'], $role_permissions_ids) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="perm_<?= $permission['id'] ?>">
                                            <strong><?= htmlspecialchars($permission['permission_name']) ?></strong>
                                            <small
                                                class="d-block text-muted"><?= htmlspecialchars($permission['description']) ?></small>
                                        </label>
                                    </div>
                                </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" name="update_permissions" class="btn btn-primary">Save Changes</button>
                    <a href="manage_roles.php" class="btn btn-secondary">Cancel</a>
                    <input type="hidden" name="role_id_confirm" value="<?= $role_id ?>">
                </div>
            </div>
        </form>
    </div>
</body>

</html>