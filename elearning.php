<?php
require_once 'includes/init.php';
require_once 'includes/theme_handler.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);

$courses = [];
$sql = "SELECT c.id, c.title, c.description, u.username as author, 
        (SELECT COUNT(*) FROM elearning_materials WHERE course_id = c.id) as material_count
        FROM elearning_courses c 
        LEFT JOIN users u ON c.created_by = u.id
        ORDER BY c.created_at DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learning Courses</title>
    <link rel="icon" type="image/png" href="uploads/EIP.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= $theme_class ?>">
    <?php include 'main_nav.php'; ?>
    <main class="container mt-4">
        <h2 class="mb-4">E-Learning Courses</h2>

        <?php if (count($courses) > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($courses as $course): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($course['description']) ?></p>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                             <small class="text-muted">
                                <i class="fas fa-book-open me-1"></i> <?= $course['material_count'] ?> Material
                            </small>
                            <a href="elearning_view.php?course_id=<?= $course['id'] ?>" class="btn btn-primary">Start Learning</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                There are no e-learning courses available at this time.
            </div>
        <?php endif; ?>
    </main>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>