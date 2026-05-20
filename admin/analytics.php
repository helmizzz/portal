<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Cek hak akses
$permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('view_analytics', $permissions)) {
    header("Location: ../dashboard.php");
    exit();
}

// 1. Dokumen Paling Sering Diakses
$top_docs_query = "
    SELECT SUBSTRING_INDEX(description, '''', -2) as doc_name, COUNT(id) as view_count 
    FROM activity_logs 
    WHERE activity_type = 'view_document' 
    GROUP BY doc_name 
    ORDER BY view_count DESC 
    LIMIT 10";
$top_docs_result = $conn->query($top_docs_query);
$top_docs = [];
while ($row = $top_docs_result->fetch_assoc())
    $top_docs[] = $row;

// 2. Pengguna Paling Aktif
$top_users_query = "
    SELECT u.username, COUNT(al.id) as activity_count
    FROM activity_logs al
    JOIN users u ON al.user_id = u.id
    GROUP BY u.username
    ORDER BY activity_count DESC
    LIMIT 10";
$top_users_result = $conn->query($top_users_query);
$top_users = [];
while ($row = $top_users_result->fetch_assoc())
    $top_users[] = $row;

// 3. Penggunaan Ruang Penyimpanan
$total_files = 0;
$total_size = 0;
$upload_dir = '../uploads/';
$files = scandir($upload_dir);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..' && !is_dir($upload_dir . $file)) {
        $total_files++;
        $total_size += filesize($upload_dir . $file);
    }
}
$total_size_mb = round($total_size / 1024 / 1024, 2);

// --- QUERY BARU UNTUK ANALITIK PENGUMUMAN ---
$top_authors_query = "
    SELECT u.username, COUNT(a.id) as announcement_count
    FROM announcements a
    JOIN users u ON a.created_by = u.id
    GROUP BY u.username
    ORDER BY announcement_count DESC
    LIMIT 5";
$top_authors_result = $conn->query($top_authors_query);
$top_authors = [];
while ($row = $top_authors_result->fetch_assoc())
    $top_authors[] = $row;

// 5. Pengumuman Paling Sering Dilihat
$top_announcements_query = "SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(description, ': \'', -1), '\'', 1) as announcement_title, COUNT(id) as view_count FROM activity_logs WHERE activity_type = 'view_announcement' GROUP BY announcement_title ORDER BY view_count DESC LIMIT 10";
$top_announcements_result = $conn->query($top_announcements_query);
$top_announcements = [];
while ($row = $top_announcements_result->fetch_assoc())
    $top_announcements[] = $row;


// Konversi data ke format JSON untuk Chart.js
$top_docs_labels = json_encode(array_column($top_docs, 'doc_name'));
$top_docs_data = json_encode(array_column($top_docs, 'view_count'));
$top_users_labels = json_encode(array_column($top_users, 'username'));
$top_users_data = json_encode(array_column($top_users, 'activity_count'));
$top_authors_labels = json_encode(array_column($top_authors, 'username'));
$top_authors_data = json_encode(array_column($top_authors, 'announcement_count'));
$top_announcements_labels = json_encode(array_column($top_announcements, 'announcement_title'));
$top_announcements_data = json_encode(array_column($top_announcements, 'view_count'));
?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Reports and Analytics</title>
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
        <h2>Reports and Analytics</h2>

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h4>Total Documents Saved</h4>
                        <p class="fs-3"><?= $total_files ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h4>Total Disk Usage</h4>
                        <p class="fs-3"><?= $total_size_mb ?> MB</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">Top 10 Most Viewed Announcements</div>
                    <div class="card-body"><canvas id="topAnnouncementsChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">10 Most Viewed Documents</div>
                    <div class="card-body"><canvas id="topDocsChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">10 Most Active Users</div>
                    <div class="card-body"><canvas id="topUsersChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">Most Active Admin Making Announcements</div>
                    <div class="card-body"><canvas id="topAuthorsChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const defaultColors = [
                'rgba(54, 162, 235, 0.6)', 'rgba(255, 99, 132, 0.6)', 'rgba(75, 192, 192, 0.6)',
                'rgba(255, 206, 86, 0.6)', 'rgba(153, 102, 255, 0.6)'
            ];

            const ctxDocs = document.getElementById('topDocsChart').getContext('2d');
            new Chart(ctxDocs, {
                type: 'bar',
                data: {
                    labels: <?= $top_docs_labels ?>,
                    datasets: [{
                        label: 'Number of Views',
                        data: <?= $top_docs_data ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: { indexAxis: 'y', responsive: true }
            });

            const ctxUsers = document.getElementById('topUsersChart').getContext('2d');
            new Chart(ctxUsers, {
                type: 'bar',
                data: {
                    labels: <?= $top_users_labels ?>,
                    datasets: [{
                        label: 'Number of Activities',
                        data: <?= $top_users_data ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: { responsive: true }
            });

            // --- SCRIPT BARU UNTUK GRAFIK PENGUMUMAN ---
            const ctxAuthors = document.getElementById('topAuthorsChart').getContext('2d');
            new Chart(ctxAuthors, {
                type: 'bar',
                data: {
                    labels: <?= $top_authors_labels ?>,
                    datasets: [{
                        label: 'Number of Announcements',
                        data: <?= $top_authors_data ?>,
                        backgroundColor: defaultColors,
                        hoverOffset: 4
                    }]
                },
                options: { responsive: true }
            });

            // PENAMBAHAN SCRIPT BARU UNTUK GRAFIK PENGUMUMAN DILIHAT
            new Chart(document.getElementById('topAnnouncementsChart'), {
                type: 'bar',
                data: { labels: <?= $top_announcements_labels ?>, datasets: [{ label: 'Number of Views', data: <?= $top_announcements_data ?>, backgroundColor: 'rgba(255, 159, 64, 0.6)' }] },
                options: { indexAxis: 'y', responsive: true }
            });
        });
    </script>
</body>

</html>