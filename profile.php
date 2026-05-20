<?php
require_once 'includes/init.php';
require_once 'includes/theme_handler.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Logika untuk mengubah password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $sql_user = "SELECT password FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_data = $stmt_user->get_result()->fetch_assoc();

    if ($user_data && password_verify($current_password, $user_data['password'])) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) { // Contoh validasi simpel
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_update = "UPDATE users SET password = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("si", $hashed_password, $user_id);
                if ($stmt_update->execute()) {
                    $message = "<div class='alert alert-success'>Password successfully changed.</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Failed to update password.</div>";
                }
            } else {
                $message = "<div class='alert alert-warning'>New password must be at least 6 characters long.</div>";
            }
        } else {
            $message = "<div class='alert alert-warning'>The new password and confirmation password do not match.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Incorrect current password.</div>";
    }
}

// Ambil detail user untuk ditampilkan
$sql_user_details = "SELECT u.username, u.profile_picture, u.is_active, r.role_name, d.name as department_name 
                     FROM users u 
                     LEFT JOIN roles r ON u.role_id = r.id 
                     LEFT JOIN departements d ON u.departement_id = d.id 
                     WHERE u.id = ?";
$stmt_details = $conn->prepare($sql_user_details);
$stmt_details->bind_param("i", $user_id);
$stmt_details->execute();
$user_details = $stmt_details->get_result()->fetch_assoc();

// Ambil riwayat aktivitas user
$activity_logs = [];
$sql_logs = "SELECT description, timestamp FROM activity_logs WHERE user_id = ? ORDER BY timestamp DESC LIMIT 50";
$stmt_logs = $conn->prepare($sql_logs);
$stmt_logs->bind_param("i", $user_id);
$stmt_logs->execute();
$result_logs = $stmt_logs->get_result();
while ($row = $result_logs->fetch_assoc()) {
    $activity_logs[] = $row;
}

// === AWAL PERUBAHAN: Ambil data kursus yang telah selesai ===
$completed_courses = [];
$sql_completed_courses = "SELECT c.id, c.title
    FROM elearning_courses c
    WHERE (
        SELECT COUNT(DISTINCT m.id)
        FROM elearning_materials m
        WHERE m.course_id = c.id
    ) > 0 AND (
        SELECT COUNT(DISTINCT ec.material_id)
        FROM elearning_completion ec
        JOIN elearning_materials em ON ec.material_id = em.id
        WHERE ec.user_id = ? AND em.course_id = c.id
    ) = (
        SELECT COUNT(DISTINCT m.id)
        FROM elearning_materials m
        WHERE m.course_id = c.id
    )
    ORDER BY c.title ASC";
$stmt_completed = $conn->prepare($sql_completed_courses);
$stmt_completed->bind_param("i", $user_id);
$stmt_completed->execute();
$result_completed = $stmt_completed->get_result();
while ($row = $result_completed->fetch_assoc()) {
    $completed_courses[] = $row;
}
// === AKHIR PERUBAHAN ===
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="icon" type="image/png" href="uploads/EIP.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= $theme_class ?>">

    <?php include 'main_nav.php'; ?>

    <main class="container mt-4">
        <h2 class="mb-4">My Profile</h2>
        <?= $message ?>
        <div class="row">
            <div class="col-lg-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><h5 class="mb-0">User Information</h5></div>
                    <div class="card-body text-center">
                        <?php
                            $profile_pic_path = 'https://via.placeholder.com/150';
                            if (!empty($user_details['profile_picture']) && file_exists('uploads/profiles/' . $user_details['profile_picture'])) {
                                $profile_pic_path = 'uploads/profiles/' . $user_details['profile_picture'];
                            }
                        ?>
                        <img src="<?= $profile_pic_path ?>" class="rounded-circle mb-3" alt="Foto Profil" style="width:150px; height:150px; object-fit: cover;">
                        <h4 class="card-title"><?= htmlspecialchars($user_details['username']) ?></h4>
                        <p class="card-text text-muted mb-1"><?= htmlspecialchars($user_details['role_name'] ?? 'No Role') ?></p>
                        <p class="card-text text-muted"><?= htmlspecialchars($user_details['department_name'] ?? 'No Department') ?></p>
                        <span class="badge bg-<?= $user_details['is_active'] ? 'success' : 'danger' ?>">
                            <?= $user_details['is_active'] ? 'Active' : 'Non-Active' ?>
                        </span>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header"><h5 class="mb-0">Completed Courses</h5></div>
                    <div class="card-body" style="max-height: 250px; overflow-y: auto;">
                        <?php if (!empty($completed_courses)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($completed_courses as $course): ?>
                                    <li class="list-group-item d-flex align-items-center">
                                        <i class="fas fa-graduation-cap text-success me-3"></i>
                                        <a href="elearning_view.php?course_id=<?= $course['id'] ?>" class="text-decoration-none stretched-link">
                                            <?= htmlspecialchars($course['title']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-center text-muted">You have not completed any courses yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card shadow-sm mb-4">
                    <div class="card-header"><h5 class="mb-0">Change Password</h5></div>
                    <div class="card-body">
                        <form action="profile.php" method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" name="current_password" id="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-header"><h5 class="mb-0">My Recent Activity</h5></div>
                    <div class="card-body" style="max-height: 858px; overflow-y: auto;">
                        <?php if (!empty($activity_logs)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($activity_logs as $log): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= htmlspecialchars($log['description']) ?>
                                        <small class="text-muted"><?= date('d M Y, H:i', strtotime($log['timestamp'])) ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-center text-muted">No activity has been recorded.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>