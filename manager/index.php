<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Pengecekan otentikasi dan role.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (empty($permissions)) {
    header("Location: ../dashboard.php");
    exit();
}

// Mengambil data statistik cepat
$total_users = $conn->query("SELECT COUNT(id) as total FROM users")->fetch_assoc()['total'];
$total_docs = $conn->query("SELECT COUNT(id) as total FROM documents")->fetch_assoc()['total'];
$total_announcements = $conn->query("SELECT COUNT(id) as total FROM announcements")->fetch_assoc()['total'];
$total_events = $conn->query("SELECT COUNT(id) as total FROM events")->fetch_assoc()['total'];
$total_polls = $conn->query("SELECT COUNT(id) as total FROM polls")->fetch_assoc()['total'];
// --- STATISTIK BARU UNTUK E-LEARNING ---
$total_courses = $conn->query("SELECT COUNT(id) as total FROM elearning_courses")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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
        <h2 class="mb-4">WELCOME, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>

        <div class="row mb-4">
            <div class="col-md-3 col-6 mb-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-graduation-cap"></i> Total E-Learning</h5>
                        <p class="card-text fs-4"><?= $total_courses ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="card text-white bg-secondary">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-bullhorn"></i> Total Announcements</h5>
                        <p class="card-text fs-4"><?= $total_announcements ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-calendar-alt"></i> Total Events</h5>
                        <p class="card-text fs-4"><?= $total_events ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-users"></i> Total Users</h5>
                        <p class="card-text fs-4"><?= $total_users ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-file-pdf"></i> Total Documents</h5>
                        <p class="card-text fs-4"><?= $total_docs ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-poll"></i> Total Polls</h5>
                        <p class="card-text fs-4"><?= $total_polls ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Navigation Menu</h4>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php if (in_array('manage_elearning', $permissions)): ?>
                                    <a href="manage_elearning.php" class="list-group-item list-group-item-action"><i
                                            class="fas fa-graduation-cap fa-fw me-2"></i>E-Learning Management</a>
                            <?php endif; ?>
                            <a href="manage_announcements.php" class="list-group-item list-group-item-action"><i
                                    class="fas fa-bullhorn fa-fw me-2"></i>Announcement Management</a>
                            <a href="manage_events.php" class="list-group-item list-group-item-action"><i
                                    class="fas fa-calendar-alt fa-fw me-2"></i>Event Calendar Management</a>
                            <a href="manage_polls.php" class="list-group-item list-group-item-action"><i
                                    class="fas fa-poll fa-fw me-2"></i>Polling Management</a>
                            <?php if (in_array('view_analytics', $permissions)): ?>
                                    <a href="analytics.php" class="list-group-item list-group-item-action"><i
                                            class="fas fa-chart-bar fa-fw me-2"></i>Reports & Analytics</a>
                            <?php endif; ?>
                            <?php if (in_array('view_elearning_report', $permissions)): ?>
                                    <a href="elearning_completion_report.php" class="list-group-item list-group-item-action"><i
                                            class="fas fa-user-check fa-fw me-2"></i>E-Learning Completion Report</a>
                            <?php endif; ?>
                            <?php if (in_array('view_analytics', $permissions)): ?>
                                    <a href="readership_report.php" class="list-group-item list-group-item-action"><i
                                            class="fas fa-eye fa-fw me-2"></i>Readership Report</a>
                            <?php endif; ?>
                            <?php if (in_array('view_audit_trail', $permissions)): ?>
                                    <a href="audit_trail.php" class="list-group-item list-group-item-action"><i
                                            class="fas fa-shoe-prints fa-fw me-2"></i>Audit Trail</a>
                            <?php endif; ?>
                            <?php if (in_array('manage_documents', $permissions)): ?>
                                    <a href="manage_documents.php" class="list-group-item list-group-item-action"><i
                                            class="fas fa-file-pdf fa-fw me-2"></i>Document & Folder Management</a>
                            <?php endif; ?>
                            <?php if (in_array('manage_users', $permissions)): ?>
                                    <a href="manage_users.php" class="list-group-item list-group-item-action"><i
                                            class="fas fa-user-cog fa-fw me-2"></i>User Management</a>
                            <?php endif; ?>
                            <?php if (in_array('manage_roles', $permissions)): ?>
                                    <a href="manage_roles.php" class="list-group-item list-group-item-action"><i
                                            class="fas fa-user-shield fa-fw me-2"></i>Role & Access Rights Management</a>
                            <?php endif; ?>
                            <a href="manage_departements.php" class="list-group-item list-group-item-action"><i
                                    class="fas fa-building fa-fw me-2"></i>Department Management</a>
                            <a href="manage_carousel.php" class="list-group-item list-group-item-action"><i
                                    class="fas fa-images fa-fw me-2"></i>Login Carousel Management</a>
                            <a href="manage_shortcuts.php" class="list-group-item list-group-item-action"><i
                                    class="fas fa-link fa-fw me-2"></i>Shortcut Management</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>