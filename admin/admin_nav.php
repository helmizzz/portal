<?php
// Membuat navigasi ini mandiri dengan memanggil theme_handler secara langsung
require_once __DIR__ . '/../includes/theme_handler.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php" style="font-size: 15px;">Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar"
            aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <?php
            // Ambil permission user dari database
            $permissions = getUserPermissions($conn, $_SESSION['user_id']);

            // Helper function untuk cek permission (optional, tapi array in_array cukup)
            // $is_admin dan $is_manager tetap disimpan untuk fallback item yang belum ada permission-nya
            $is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin');
            $is_manager = (isset($_SESSION['role']) && $_SESSION['role'] === 'Manager');
            ?>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (in_array('manage_announcements', $permissions)): // Belum ada permission khusus di DB ?>
                    <li class="nav-item"><a class="nav-link" href="manage_announcements.php">Announcement</a></li>
                <?php endif; ?>

                <?php if (in_array('manage_events', $permissions)): ?>
                    <li class="nav-item"><a class="nav-link" href="manage_events.php">Event</a></li>
                <?php endif; ?>

                <?php if (in_array('manage_polls', $permissions)): ?>
                    <li class="nav-item"><a class="nav-link" href="manage_polls.php">Polling</a></li>
                <?php endif; ?>

                <?php if (in_array('manage_elearning', $permissions)): ?>
                    <li class="nav-item"><a class="nav-link" href="manage_elearning.php">E-Learn</a></li>
                <?php endif; ?>

                <?php if (in_array('view_analytics', $permissions)): ?>
                    <li class="nav-item"><a class="nav-link" href="analytics.php">Analytic</a></li>
                <?php endif; ?>

                <?php if (in_array('view_elearning_report', $permissions)): ?>
                    <li class="nav-item"><a class="nav-link" href="elearning_completion_report.php">E-Learn Report</a></li>
                    <li class="nav-item"><a class="nav-link" href="readership_report.php">Read Report</a></li>
                <?php endif; ?>

                <?php if (in_array('view_audit_trail', $permissions)): ?>
                    <li class="nav-item"><a class="nav-link" href="audit_trail.php">Audit Trail</a></li>
                <?php endif; ?>

                <?php if (in_array('manage_documents', $permissions)): ?>
                    <!-- <li class="nav-item"><a class="nav-link" href="manage_documents.php">Document</a></li> -->
                    <li class="nav-item"><a class="nav-link" href="manage_documentsrevbaru.php">Document</a></li>
                <?php endif; ?>

                <?php if (in_array('manage_users', $permissions)): ?>
                    <li class="nav-item"><a class="nav-link" href="manage_users.php">Users</a></li>
                <?php endif; ?>

                <?php if (in_array('manage_roles', $permissions)): ?>
                    <li class="nav-item"><a class="nav-link" href="manage_roles.php">Role</a></li>
                <?php endif; ?>

                <?php if ($is_admin): // Belum ada permission khusus di DB ?>
                    <li class="nav-item"><a class="nav-link" href="manage_departements.php">Department</a></li>
                <?php endif; ?>

                <?php if ($is_admin): // Belum ada permission khusus di DB ?>
                    <li class="nav-item"><a class="nav-link" href="manage_carousel.php">Carousel</a></li>
                <?php endif; ?>

                <?php if ($is_admin): // Belum ada permission khusus di DB ?>
                    <li class="nav-item"><a class="nav-link" href="manage_shortcuts.php">Shortcut</a></li>
                <?php endif; ?>
                <?php if ($is_admin): ?>
                    <li class="nav-item"><a class="nav-link" href="restore.php"><i class="fas fa-trash"></i></a></li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center">
                <div class="form-check form-switch me-3">
                    <input class="form-check-input" type="checkbox" id="theme-switch-checkbox" <?= $is_dark_theme ? 'checked' : '' ?>>
                    <label class="form-check-label text-white" for="theme-switch-checkbox"><i
                            class="fas fa-moon"></i></label>
                </div>
                <a href="../dashboard.php" class="btn btn-outline-secondary me-2">User Dashboard</a>
                <a href="../logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </div>
</nav>