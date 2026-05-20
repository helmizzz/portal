<?php
require_once '../includes/theme_handler.php'; // Pola 1: Panggil theme_handler
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
// bug fix hak akses, +manage_departements
$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('manage_departements', $user_permissions) && $_SESSION['role'] !== 'Admin') {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
    header("Location: ../dashboard.php"); // Atau kembali ke dashboard jika login tapi tidak punya akses
    exit();
}

// Logika untuk menambahkan departemen baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_departement') {
    $departement_name = $_POST['departement_name'];
    if (!empty($departement_name)) {
        $sql = "INSERT INTO departements (name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $departement_name);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Departemen berhasil ditambahkan.</div>";
        } else {
            $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal menambahkan departemen.</div>";
        }
        header("Location: manage_departements.php");
        exit();
    }
}

// Logika untuk menghapus departemen
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $sql = "DELETE FROM departements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "<div class='alert alert-success'>Departemen berhasil dihapus.</div>";
    } else {
        $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal menghapus departemen. Pastikan tidak ada pengguna atau dokumen yang terkait.</div>";
    }
    header("Location: manage_departements.php");
    exit();
}

// Ambil semua data departemen untuk ditampilkan
$departements = [];
$sql = "SELECT id, name FROM departements ORDER BY name ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departements[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Department Management</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/theme.js" defer></script>
</head>

<body class="<?= $theme_class ?>"> <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <h2>Department Management</h2>

        <?= $message ?>

        <div class="card mb-4">
            <div class="card-header">Add New Department</div>
            <div class="card-body">
                <form action="manage_departements.php" method="POST">
                    <input type="hidden" name="action" value="add_departement">
                    <div class="mb-3">
                        <label for="departement_name" class="form-label">Department Name</label>
                        <input type="text" name="departement_name" id="departement_name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Department List</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Department Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($departements) > 0): ?>
                                    <?php foreach ($departements as $dept): ?>
                                            <tr>
                                                <td><?= $dept['id'] ?></td>
                                                <td><?= htmlspecialchars($dept['name']) ?></td>
                                                <td>
                                                    <a href="manage_departements.php?action=delete&id=<?= $dept['id'] ?>"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus departemen ini?');">Delete</a>
                                                </td>
                                            </tr>
                                    <?php endforeach; ?>
                            <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">There is no department.</td>
                                    </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>