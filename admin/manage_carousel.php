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
// bug fix hak akses, +manage_documents
$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('manage_carousel', $user_permissions) && $_SESSION['role'] !== 'Admin') {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
    header("Location: ../dashboard.php"); // Atau kembali ke dashboard jika login tapi tidak punya akses
    exit();
}
// Logika untuk mengunggah gambar carousel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_carousel_image') {
    $caption = $_POST['caption'];

    if (isset($_FILES['carousel_image']) && $_FILES['carousel_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['carousel_image']['tmp_name'];
        $file_name = $_FILES['carousel_image']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array(strtolower($file_ext), $allowed_ext)) {
            $message = "<div class='alert alert-danger'>Hanya file gambar JPG, JPEG, PNG, & GIF yang diizinkan.</div>";
        } else {
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_dir = '../uploads/carousel/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $dest_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $sql = "INSERT INTO carousel_images (image_name, caption) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ss", $new_file_name, $caption);
                    if ($stmt->execute()) {
                        $message = "<div class='alert alert-success'>Gambar carousel berhasil diunggah.</div>";
                    } else {
                        $message = "<div class='alert alert-danger'>Gagal menyimpan data gambar ke database.</div>";
                    }
                } else {
                    $message = "<div class='alert alert-danger'>Kesalahan database.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>Gagal memindahkan file yang diunggah.</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-danger'>Terjadi kesalahan saat mengunggah file.</div>";
    }
}

// Logika untuk menghapus gambar carousel
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Ambil nama file untuk dihapus
    $sql_img = "SELECT image_name FROM carousel_images WHERE id = ?";
    $stmt_img = $conn->prepare($sql_img);
    $stmt_img->bind_param("i", $id);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    $image = $result_img->fetch_assoc();

    if ($image) {
        $filePath = '../uploads/carousel/' . $image['image_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $sql_delete = "DELETE FROM carousel_images WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $id);
        $stmt_delete->execute();
    }
    header("Location: manage_carousel.php");
    exit();
}

// Ambil semua gambar carousel untuk ditampilkan
$carousel_images = [];
$sql = "SELECT id, image_name, caption FROM carousel_images ORDER BY id DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $carousel_images[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Login Carousel Management</title>
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
        <h2>Login Carousel Management</h2>

        <?= $message ?>

        <div class="card mb-4">
            <div class="card-header">Upload New Image</div>
            <div class="card-body">
                <form action="manage_carousel.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_carousel_image">
                    <div class="mb-3">
                        <label for="carousel_image" class="form-label">Select Image</label>
                        <input type="file" name="carousel_image" id="carousel_image" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="caption" class="form-label">Description Text (Optional)</label>
                        <input type="text" name="caption" id="caption" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Carousel Image List</div>
            <div class="card-body">
                <div class="row">
                    <?php if (count($carousel_images) > 0): ?>
                            <?php foreach ($carousel_images as $image): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <img src="../uploads/carousel/<?= htmlspecialchars($image['image_name']) ?>"
                                                class="card-img-top" alt="<?= htmlspecialchars($image['caption']) ?>">
                                            <div class="card-body text-center">
                                                <p class="card-text"><?= htmlspecialchars($image['caption'] ?: 'Tanpa Deskripsi') ?></p>
                                                <a href="manage_carousel.php?action=delete&id=<?= $image['id'] ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus gambar ini?');">Delete</a>
                                            </div>
                                        </div>
                                    </div>
                            <?php endforeach; ?>
                    <?php else: ?>
                            <div class="col-12">
                                <p class="text-center">There are no images in the carousel.</p>
                            </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>