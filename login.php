<?php
require_once 'includes/init.php';
require_once 'includes/db_connect.php';

// Ambil data gambar carousel dari database
$carousel_images = [];
$sql_carousel = "SELECT image_name, caption FROM carousel_images";
$result_carousel = $conn->query($sql_carousel);
if ($result_carousel) {
    while ($row = $result_carousel->fetch_assoc()) {
        $carousel_images[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query diperbarui untuk mengambil role_name, is_active, dan theme
    $sql = "SELECT u.id, u.username, u.password, r.role_name, u.is_active, u.theme, u.departement_id, d.name as departement_name
            FROM users u 
            LEFT JOIN roles r ON u.role_id = r.id 
            LEFT JOIN departements d ON u.departement_id = d.id
            WHERE u.username = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password'])) {

            // PENGECEKAN BARU: PERIKSA APAKAH AKUN AKTIF
            if ($user['is_active'] == 0) {
                $error = "Akun Anda tidak aktif. Silakan hubungi Administrator.";
            } else {
                // Jika aktif, lanjutkan proses login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role_name'];
                $_SESSION['theme'] = $user['theme']; // Simpan tema ke session
                $_SESSION['departement_id'] = $user['departement_id'];
                $_SESSION['departement_name'] = $user['departement_name'];

                if ($user['role_name'] === 'Admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            }
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" type="image/png" href="uploads/EIP.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .login-container {
            height: 100vh;
        }

        .carousel-item img {
            object-fit: cover;
            height: 100vh;
            width: 100%;
        }

        .login-form-container {
            background-color: #fff;
            padding: 2rem;
            border-radius: .5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .carousel-container {
                display: none;
            }

            .login-container .row {
                justify-content: center;
            }

            .login-container .col-md-2 {
                flex: 0 0 90%;
                max-width: 90%;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid login-container">
        <div class="row h-100">
            <div class="col-md-10 d-none d-md-flex p-0 carousel-container">
                <div id="carouselExampleIndicators" class="carousel slide h-100 w-100" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <?php for ($i = 0; $i < count($carousel_images); $i++): ?>
                            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="<?= $i ?>"
                                class="<?= $i === 0 ? 'active' : '' ?>"></button>
                        <?php endfor; ?>
                    </div>
                    <div class="carousel-inner h-100">
                        <?php if (count($carousel_images) > 0): ?>
                            <?php foreach ($carousel_images as $index => $image): ?>
                                <div class="carousel-item h-100 <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="uploads/carousel/<?= htmlspecialchars($image['image_name']) ?>"
                                        class="d-block w-100"
                                        alt="<?= htmlspecialchars($image['caption'] ?: 'Carousel Image') ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="carousel-item active h-100">
                                <img src="https://via.placeholder.com/1920x1080.png?text=Tidak+ada+gambar"
                                    class="d-block w-100" alt="Placeholder">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-2 d-flex justify-content-center align-items-center">
                <div class="card shadow-lg p-4 w-100">
                    <h2 class="card-title text-center mb-4">Login</h2>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3"><label for="username" class="form-label">Username</label><input type="text"
                                id="username" name="username" class="form-control" required></div>
                        <div class="mb-3"><label for="password" class="form-label">Password</label><input
                                type="password" id="password" name="password" class="form-control" required></div>
                        <div class="d-grid gap-2"><button type="submit" class="btn btn-primary btn-lg">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>