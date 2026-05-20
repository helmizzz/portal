<?php
require_once 'includes/init.php';
// Memastikan variabel tema selalu tersedia saat navigasi ini dipanggil
require_once __DIR__ . '/includes/theme_handler.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">EIP</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="elearning.php">E-Learning</a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" href="sop.php">SOP & IK</a>
                </li> -->
                <li class="nav-item">
                    <a class="nav-link" href="sop1.php">SOP & IK</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="calendar.php">Event Calendar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="employee_directory.php">Employee Directory</a>
                </li>
            </ul>

            <div class="d-flex align-items-center">
                <form class="d-flex me-3" action="search_results.php" method="GET">
                    <input class="form-control form-control-sm" type="search" name="q" placeholder="Search..."
                        aria-label="Search" required>
                    <button class="btn btn-outline-light btn-sm" type="submit"><i class="fas fa-search"></i></button>
                </form>

                <div class="nav-item me-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="theme-switch-checkbox" <?= $is_dark_theme ? 'checked' : '' ?>>
                        <label class="form-check-label text-white" for="theme-switch-checkbox">
                            <i class="fas fa-moon"></i>
                        </label>
                    </div>
                </div>

                <div class="nav-item me-3">
                    <div class="notification-bell text-white fs-5" id="notification-bell-icon" style="cursor: pointer;">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger" id="notification-count"
                            style="display: none; position: absolute; top: -5px; right: -10px; font-size: 0.6rem; padding: 3px 5px; border-radius: 50%;"></span>
                    </div>
                    <div class="dropdown-menu dropdown-menu-end notifications-dropdown" id="notifications-container">
                        <div class="notifications-dropdown-header">Notification</div>
                        <div id="notification-list"></div>
                    </div>
                </div>

                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user me-1"></i>
                        <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>My
                                Profile</a></li>
                        <?php if (isset($user_permissions) && (in_array('manage_documents', $user_permissions) || in_array('manage_elearning', $user_permissions))): ?>
                            <li><a class="dropdown-item" href="admin/index.php"><i class="fas fa-cogs me-2"></i>Admin
                                    Panel</a></li>
                        <?php endif; ?>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="logout.php"><i
                                    class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const themeSwitch = document.getElementById('theme-switch-checkbox');

        const setThemeOnBody = (theme) => {
            if (theme === 'dark') {
                document.body.classList.add('dark-mode');
            } else {
                document.body.classList.remove('dark-mode');
            }
            localStorage.setItem('theme', theme);
        };

        const saveThemePreference = (theme) => {
            fetch('api/save_theme.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ theme: theme })
            }).catch(err => console.error('Gagal menyimpan tema:', err));
        };

        if (themeSwitch) {
            themeSwitch.addEventListener('change', () => {
                const newTheme = themeSwitch.checked ? 'dark' : 'light';
                setThemeOnBody(newTheme);
                saveThemePreference(newTheme);
            });

            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                setThemeOnBody(savedTheme);
                themeSwitch.checked = savedTheme === 'dark';
            }
        }
    });
</script>