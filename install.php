<?php
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "portalrev";
$sql_file = 'portal.sql';

// Cek apakah file .sql ada
$sql_file_exists = file_exists($sql_file);

$message = '';
$status = $_GET['status'] ?? '';
$action = $_POST['action'] ?? '';

@$conn_server = new mysqli($db_host, $db_user, $db_pass);

if ($action) {
    if ($conn_server->connect_error) {
        $message = "<div class='alert alert-danger'><strong>Connection Failed!</strong> Make sure your database server (MySQL) is running and the connection details are correct.</div>";
    } else {
        if ($action === 'create_db' || $action === 'import_tables') {
            if (!$sql_file_exists) {
                $message = "<div class='alert alert-danger'><strong>Action Failed!</strong> File `portal.sql` not found to perform import.</div>";
            } else {
                $conn_server->query("CREATE DATABASE IF NOT EXISTS `$db_name`");
                $conn_server->select_db($db_name);
                $sql_content = file_get_contents($sql_file);
                if ($conn_server->multi_query($sql_content)) {
                    while ($conn_server->more_results() && $conn_server->next_result()) {
                        ;
                    }
                    $message = "<div class='alert alert-success'><strong>Installation Successful!</strong> The database and tables from the file were successfully imported.</div>";
                    $status = 'success';
                } else {
                    $message = "<div class='alert alert-danger'>Failed to import from file: " . $conn_server->error . "</div>";
                }
            }
        } elseif ($action === 'create_structure_only') {
            try {
                $conn_server->query("CREATE DATABASE IF NOT EXISTS `$db_name`");
                $conn_server->select_db($db_name);

                // Kumpulan perintah SQL untuk membuat struktur tabel lengkap
                $queries = [
                    // CREATE TABLES
                    "CREATE TABLE `activity_logs` (`id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `activity_type` varchar(255) NOT NULL, `description` text NOT NULL, `document_id` int(11) DEFAULT NULL, `folder_id` int(11) DEFAULT NULL, `timestamp` timestamp NOT NULL DEFAULT current_timestamp()) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `announcements` (`id` int(11) NOT NULL, `title` varchar(255) NOT NULL, `content` text NOT NULL, `attachment_file` varchar(255) DEFAULT NULL, `start_date` datetime DEFAULT NULL, `end_date` datetime DEFAULT NULL, `created_by` int(11) DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp(), `is_pinned` tinyint(1) NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `carousel_images` (`id` int(11) NOT NULL, `image_name` varchar(255) NOT NULL, `caption` varchar(255) DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `departements` (`id` int(11) NOT NULL, `name` varchar(100) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `documents` (`id` int(11) NOT NULL, `title` varchar(255) NOT NULL, `file_name` varchar(255) NOT NULL, `folder_id` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `document_departments` (`document_id` int(11) NOT NULL, `departement_id` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `document_permissions` (`id` int(11) NOT NULL, `document_id` int(11) NOT NULL, `user_id` int(11) DEFAULT NULL, `departement_id` int(11) DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `document_user_access` (`document_id` int(11) NOT NULL, `user_id` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `document_versions` (`id` int(11) NOT NULL, `document_id` int(11) NOT NULL, `version_file_name` varchar(255) NOT NULL, `notes` text DEFAULT NULL, `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(), `user_id` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `elearning_completion` (`id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `material_id` int(11) NOT NULL, `completed_at` timestamp NOT NULL DEFAULT current_timestamp()) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `elearning_courses` (`id` int(11) NOT NULL, `title` varchar(255) NOT NULL, `description` text DEFAULT NULL, `share_token` varchar(32) DEFAULT NULL, `created_by` int(11) DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp()) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `elearning_materials` (`id` int(11) NOT NULL, `course_id` int(11) NOT NULL, `title` varchar(255) NOT NULL, `content_type` enum('text','video') NOT NULL, `content_text` text DEFAULT NULL, `video_file_name` varchar(255) DEFAULT NULL, `sort_order` int(11) NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `events` (`id` int(11) NOT NULL, `title` varchar(255) NOT NULL, `description` text DEFAULT NULL, `start_datetime` datetime NOT NULL, `end_datetime` datetime DEFAULT NULL, `event_color` varchar(20) DEFAULT '#3788d8', `created_by` int(11) DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp()) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `folders` (`id` int(11) NOT NULL, `name` varchar(255) NOT NULL, `parent_id` int(11) DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `folder_departments` (`folder_id` int(11) NOT NULL, `departement_id` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `folder_user_access` (`folder_id` int(11) NOT NULL, `user_id` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `notifications` (`id` int(11) NOT NULL, `user_id` int(11) NOT NULL, `message` text NOT NULL, `link` varchar(255) DEFAULT NULL, `is_read` tinyint(1) NOT NULL DEFAULT 0, `created_at` timestamp NOT NULL DEFAULT current_timestamp()) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `permissions` (`id` int(11) NOT NULL, `permission_name` varchar(255) NOT NULL, `description` text DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `polls` (`id` int(11) NOT NULL, `title` varchar(255) NOT NULL, `description` text DEFAULT NULL, `status` enum('active','closed') NOT NULL DEFAULT 'active', `share_token` varchar(32) DEFAULT NULL, `created_by` int(11) DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp()) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `poll_options` (`id` int(11) NOT NULL, `question_id` int(11) NOT NULL, `option_text` varchar(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `poll_questions` (`id` int(11) NOT NULL, `poll_id` int(11) NOT NULL, `question_text` text NOT NULL, `sort_order` int(11) NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `poll_responses` (`id` int(11) NOT NULL, `poll_id` int(11) NOT NULL, `question_id` int(11) NOT NULL, `option_id` int(11) NOT NULL, `user_id` int(11) DEFAULT NULL, `ip_address` varchar(45) NOT NULL, `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `public_announcements` (`id` int(11) NOT NULL, `announcement_id` int(11) NOT NULL, `token` varchar(32) NOT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp()) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `roles` (`id` int(11) NOT NULL, `role_name` varchar(255) NOT NULL, `description` text DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `role_permissions` (`role_id` int(11) NOT NULL, `permission_id` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `shared_links` (`id` int(11) NOT NULL, `document_id` int(11) NOT NULL, `token` varchar(64) NOT NULL, `password` varchar(255) DEFAULT NULL, `expires_at` datetime DEFAULT NULL, `created_by` int(11) DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp()) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `shortcuts` (`id` int(11) NOT NULL, `name` varchar(255) NOT NULL, `url` varchar(500) NOT NULL, `icon_class` varchar(255) NOT NULL, `sort_order` int(11) DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `users` (`id` int(11) NOT NULL, `username` varchar(50) NOT NULL, `password` varchar(255) NOT NULL, `role_id` int(11) DEFAULT 2, `profile_picture` varchar(255) DEFAULT NULL, `is_active` tinyint(1) NOT NULL DEFAULT 1, `departement_id` int(11) DEFAULT NULL, `theme` varchar(10) NOT NULL DEFAULT 'light') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
                    "CREATE TABLE `user_favorite_documents` (`user_id` int(11) NOT NULL, `document_id` int(11) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

                    // ADD PRIMARY KEYS & INDEXES
                    "ALTER TABLE `activity_logs` ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);",
                    "ALTER TABLE `announcements` ADD PRIMARY KEY (`id`), ADD KEY `fk_announcements_user` (`created_by`);",
                    "ALTER TABLE `carousel_images` ADD PRIMARY KEY (`id`);",
                    "ALTER TABLE `departements` ADD PRIMARY KEY (`id`);",
                    "ALTER TABLE `documents` ADD PRIMARY KEY (`id`), ADD KEY `folder_id` (`folder_id`);",
                    "ALTER TABLE `document_departments` ADD PRIMARY KEY (`document_id`,`departement_id`), ADD KEY `departement_id` (`departement_id`);",
                    "ALTER TABLE `document_permissions` ADD PRIMARY KEY (`id`), ADD KEY `document_id` (`document_id`), ADD KEY `user_id` (`user_id`), ADD KEY `departement_id` (`departement_id`);",
                    "ALTER TABLE `document_user_access` ADD PRIMARY KEY (`document_id`,`user_id`), ADD KEY `user_id` (`user_id`);",
                    "ALTER TABLE `document_versions` ADD PRIMARY KEY (`id`), ADD KEY `document_id` (`document_id`), ADD KEY `user_id` (`user_id`);",
                    "ALTER TABLE `elearning_completion` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `user_material_unique` (`user_id`,`material_id`), ADD KEY `material_id` (`material_id`);",
                    "ALTER TABLE `elearning_courses` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `share_token` (`share_token`), ADD KEY `created_by` (`created_by`);",
                    "ALTER TABLE `elearning_materials` ADD PRIMARY KEY (`id`), ADD KEY `course_id` (`course_id`);",
                    "ALTER TABLE `events` ADD PRIMARY KEY (`id`), ADD KEY `fk_events_user` (`created_by`);",
                    "ALTER TABLE `folders` ADD PRIMARY KEY (`id`), ADD KEY `parent_id` (`parent_id`);",
                    "ALTER TABLE `folder_departments` ADD PRIMARY KEY (`folder_id`,`departement_id`), ADD KEY `departement_id` (`departement_id`);",
                    "ALTER TABLE `folder_user_access` ADD PRIMARY KEY (`folder_id`,`user_id`), ADD KEY `user_id` (`user_id`);",
                    "ALTER TABLE `notifications` ADD PRIMARY KEY (`id`), ADD KEY `user_id` (`user_id`);",
                    "ALTER TABLE `permissions` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `permission_name` (`permission_name`);",
                    "ALTER TABLE `polls` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `share_token` (`share_token`), ADD KEY `created_by` (`created_by`);",
                    "ALTER TABLE `poll_options` ADD PRIMARY KEY (`id`), ADD KEY `question_id` (`question_id`);",
                    "ALTER TABLE `poll_questions` ADD PRIMARY KEY (`id`), ADD KEY `poll_id` (`poll_id`);",
                    "ALTER TABLE `poll_responses` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `unique_vote` (`poll_id`,`question_id`,`ip_address`), ADD KEY `poll_id` (`poll_id`), ADD KEY `question_id` (`question_id`), ADD KEY `option_id` (`option_id`), ADD KEY `user_id` (`user_id`);",
                    "ALTER TABLE `public_announcements` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `token` (`token`), ADD KEY `announcement_id` (`announcement_id`);",
                    "ALTER TABLE `roles` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `role_name` (`role_name`);",
                    "ALTER TABLE `role_permissions` ADD PRIMARY KEY (`role_id`,`permission_id`), ADD KEY `permission_id` (`permission_id`);",
                    "ALTER TABLE `shared_links` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `token` (`token`), ADD KEY `document_id` (`document_id`), ADD KEY `fk_shared_links_created_by` (`created_by`);",
                    "ALTER TABLE `shortcuts` ADD PRIMARY KEY (`id`);",
                    "ALTER TABLE `users` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `username` (`username`), ADD KEY `users_ibfk_2` (`role_id`);",
                    "ALTER TABLE `user_favorite_documents` ADD PRIMARY KEY (`user_id`,`document_id`), ADD KEY `document_id` (`document_id`);",

                    // AUTO_INCREMENT
                    "ALTER TABLE `activity_logs` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `announcements` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `carousel_images` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `departements` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `documents` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `document_permissions` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `document_versions` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `elearning_completion` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `elearning_courses` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `elearning_materials` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `events` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `folders` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `notifications` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `permissions` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `polls` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `poll_options` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `poll_questions` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `poll_responses` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `public_announcements` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `roles` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `shared_links` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `shortcuts` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",
                    "ALTER TABLE `users` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;",

                    // FOREIGN KEY CONSTRAINTS
                    "ALTER TABLE `activity_logs` ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `announcements` ADD CONSTRAINT `fk_announcements_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;",
                    "ALTER TABLE `documents` ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `document_departments` ADD CONSTRAINT `dd_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `dd_ibfk_2` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `document_permissions` ADD CONSTRAINT `dp_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `dp_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `dp_ibfk_3` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `document_user_access` ADD CONSTRAINT `dua_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `dua_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `document_versions` ADD CONSTRAINT `dv_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `dv_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `elearning_completion` ADD CONSTRAINT `ec_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `ec_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `elearning_materials` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `elearning_courses` ADD CONSTRAINT `elc_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;",
                    "ALTER TABLE `elearning_materials` ADD CONSTRAINT `elm_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `elearning_courses` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `events` ADD CONSTRAINT `fk_events_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;",
                    "ALTER TABLE `folders` ADD CONSTRAINT `folders_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `folder_departments` ADD CONSTRAINT `fd_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `fd_ibfk_2` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `folder_user_access` ADD CONSTRAINT `fua_ibfk_1` FOREIGN KEY (`folder_id`) REFERENCES `folders` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `fua_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `notifications` ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `polls` ADD CONSTRAINT `polls_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;",
                    "ALTER TABLE `poll_options` ADD CONSTRAINT `po_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `poll_questions` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `poll_questions` ADD CONSTRAINT `pq_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `poll_responses` ADD CONSTRAINT `pr_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `pr_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `poll_questions` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `pr_ibfk_3` FOREIGN KEY (`option_id`) REFERENCES `poll_options` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `pr_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;",
                    "ALTER TABLE `public_announcements` ADD CONSTRAINT `fk_pa_id` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `role_permissions` ADD CONSTRAINT `rp_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `rp_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;",
                    "ALTER TABLE `shared_links` ADD CONSTRAINT `sl_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `sl_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;",
                    "ALTER TABLE `users` ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;",
                    "ALTER TABLE `user_favorite_documents` ADD CONSTRAINT `ufd_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE, ADD CONSTRAINT `ufd_ibfk_2` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;",

                    // DATA MINIMAL PENTING
                    "INSERT INTO `roles` (`id`, `role_name`, `description`) VALUES (1, 'Admin', 'Akses penuh ke semua fitur manajemen.'), (2, 'Staff', 'Akses dasar untuk melihat dokumen.'), (3, 'Manager', 'Dapat melihat semua dokumen dan laporan, tapi tidak bisa mengubah user.');",
                    "INSERT INTO `permissions` (`id`, `permission_name`, `description`) VALUES (1, 'manage_documents', 'Dapat menambah, mengedit, dan menghapus dokumen/folder.'), (2, 'manage_users', 'Dapat menambah, mengedit, dan menghapus pengguna.'), (3, 'manage_roles', 'Dapat mengelola peran dan hak aksesnya.'), (4, 'view_all_documents', 'Dapat melihat semua dokumen, terlepas dari departemen.'), (5, 'view_audit_trail', 'Dapat melihat halaman jejak audit (audit trail).'), (6, 'view_analytics', 'Dapat melihat halaman laporan dan analitik.'), (7, 'manage_events', 'Dapat menambah, mengedit, dan menghapus acara di kalender.'), (8, 'manage_polls', 'Dapat membuat, mengedit, dan melihat hasil polling atau survei.'), (9, 'manage_elearning', 'Dapat membuat, mengedit, dan menghapus kursus e-learning beserta materinya.'), (10, 'view_elearning_report', 'Dapat melihat laporan penyelesaian kursus e-learning.');",
                    "INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES (1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (3, 4), (3, 6);",
                    "INSERT INTO `users` (`id`, `username`, `password`, `role_id`) VALUES (1, 'admin', '" . password_hash('admin', PASSWORD_DEFAULT) . "', 1);"
                ];

                foreach ($queries as $query) {
                    if (!$conn_server->query($query)) {
                        throw new Exception("Failed to execute query: " . $conn_server->error . "<br>Query: " . htmlspecialchars($query));
                    }
                }

                $message = "<div class='alert alert-success'><strong>Installation Successful!</strong> An empty database structure was successfully created. Default account <strong>admin</strong> with a password <strong>admin</strong> has been added.</div>";
                $status = 'success';

            } catch (Exception $e) {
                $message = "<div class='alert alert-danger'>There is an error: " . $e->getMessage() . "</div>";
            }
        }
    }
    if ($action)
        $conn_server->close();
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Information Portal Installation</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f2f5;
        }

        .installer-container {
            max-width: 700px;
            margin: 5% auto;
        }
    </style>
</head>

<body>
    <div class="container installer-container">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h3 class="text-center mb-0">Information Portal Installation Setup</h3>
            </div>
            <div class="card-body p-4">
                <?php if ($message)
                    echo $message; ?>

                <?php if ($status !== 'success'): ?>
                    <p class="text-center">WELCOME! This script will help you set up the database for the application.</p>
                    <hr>
                    <div class="text-center">
                        <?php if ($status === 'no_db'): ?>
                            <p>Database <strong>'<?= $db_name ?>'</strong> not found.</p>
                            <?php if ($sql_file_exists): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="create_db">
                                    <button type="submit" class="btn btn-primary btn-lg">Create & Import from
                                        `<?= $sql_file ?>`</button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-warning">File `<?= $sql_file ?>` not found. You can create an empty database
                                    structure.</div>
                                <form method="POST">
                                    <input type="hidden" name="action" value="create_structure_only">
                                    <button type="submit" class="btn btn-secondary btn-lg">Create an Empty Database
                                        Structure</button>
                                </form>
                            <?php endif; ?>

                        <?php elseif ($status === 'no_tables'): ?>
                            <p>Database <strong>'<?= $db_name ?>'</strong> detected, but it still seems empty.</p>
                            <?php if ($sql_file_exists): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="import_tables">
                                    <button type="submit" class="btn btn-success btn-lg">Import Table from
                                        `<?= $sql_file ?>`</button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-warning">File `<?= $sql_file ?>` not found. You can create a basic table
                                    structure.</div>
                                <form method="POST">
                                    <input type="hidden" name="action" value="create_structure_only">
                                    <button type="submit" class="btn btn-secondary btn-lg">Create an Empty Table Structure</button>
                                </form>
                            <?php endif; ?>

                        <?php elseif ($status === 'no_server'): ?>
                            <div class='alert alert-danger'><strong>Connection Failed!</strong> Unable to connect to the
                                database server (MySQL). Ensure the server is running and the connection details in
                                `install.php` are correct..</div>
                        <?php else: ?>
                            <div class='alert alert-info'>Status unknown. Please check your configuration.</div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <a href="login.php" class="btn btn-success btn-lg">Proceed to Login Page</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>