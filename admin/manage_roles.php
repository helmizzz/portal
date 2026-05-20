<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Cek hak akses
$permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('manage_roles', $permissions)) {
    header("Location: ../dashboard.php");
    exit();
}

$message = '';

// Logika Tambah Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_role'])) {
    $role_name = trim($_POST['role_name']);
    $description = trim($_POST['description']);
    if (!empty($role_name)) {
        $sql = "INSERT INTO roles (role_name, description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $role_name, $description);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Peran baru berhasil ditambahkan.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Gagal menambahkan peran. Mungkin nama sudah ada.</div>";
        }
    }
}

// Logika Hapus Role
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $role_id = (int) $_GET['id'];
    // Cegah penghapusan role Admin & User bawaan
    if ($role_id > 2) {
        $sql = "DELETE FROM roles WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        header("Location: manage_roles.php");
        exit();
    }
}

// Ambil semua role
$roles = [];
$result = $conn->query("SELECT * FROM roles ORDER BY id");
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Role & Access Rights Management</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="../assets/js/theme.js" defer></script>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <h2>Role * Access Rights Management</h2>
        <?= $message ?>

        <div class="card mb-4">
            <div class="card-header">Add New Role</div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-5"><input type="text" name="role_name" class="form-control"
                                placeholder="Role Name" required></div>
                        <div class="col-md-5"><input type="text" name="description" class="form-control"
                                placeholder="Short Description"></div>
                        <div class="col-md-2"><button type="submit" name="add_role"
                                class="btn btn-primary w-100">Add</button></div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Role List</div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td><?= $role['id'] ?></td>
                                    <td><?= htmlspecialchars($role['role_name']) ?></td>
                                    <td><?= htmlspecialchars($role['description']) ?></td>
                                    <td>
                                        <a href="edit_role_permissions.php?role_id=<?= $role['id'] ?>"
                                            class="btn btn-warning btn-sm">Modify Access Rights</a>
                                        <?php if ($role['id'] > 2): // Hanya role custom yang bisa dihapus ?>
                                                <a href="manage_roles.php?action=delete&id=<?= $role['id'] ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Yakin ingin menghapus peran ini?')">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>