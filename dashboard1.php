<?php
require_once 'includes/init.php';
require_once 'includes/theme_handler.php'; // Mengikat Talisman Inti
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_permissions = getUserPermissions($conn, $user_id);

$sql_user = "SELECT u.username, u.role_id, u.profile_picture, d.name as department_name 
             FROM users u 
             LEFT JOIN departements d ON u.departement_id = d.id 
             WHERE u.id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();

// Ambil dokumen favorit
$favorite_docs = [];
$sql_fav = "SELECT d.id, d.title, d.file_name 
            FROM user_favorite_documents f
            JOIN documents d ON f.document_id = d.id
            WHERE f.user_id = ? 
            ORDER BY d.title ASC";
$stmt_fav = $conn->prepare($sql_fav);
if ($stmt_fav) {
    $stmt_fav->bind_param("i", $user_id);
    $stmt_fav->execute();
    $result_fav = $stmt_fav->get_result();
    while ($row = $result_fav->fetch_assoc()) {
        $favorite_docs[] = $row;
    }
} else {
    // Log error for debugging
    error_log("Failed to prepare favorite documents query: " . $conn->error);
}

// === AWAL PERUBAHAN: Ambil data shortcut dari database ===
$shortcuts = [];
$sql_shortcuts = "SELECT name, url, icon_class FROM shortcuts ORDER BY sort_order ASC";
$result_shortcuts = $conn->query($sql_shortcuts);
if ($result_shortcuts) {
    while ($row = $result_shortcuts->fetch_assoc()) {
        $shortcuts[] = $row;
    }
}
// === AKHIR PERUBAHAN ===

$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
?>
<!DOCTYPE html>
<html lang="id" data-api-path="">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="icon" type="image/png" href="uploads/EIP.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/theme.js" defer></script>

    <style>
        .announcement-item:hover {
            background-color: #f8f9fa;
        }

        .pin-icon {
            color: #ffc107;
        }

        .profile-pic-container {
            position: relative;
            display: inline-block;
        }

        .profile-pic-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .profile-pic-container:hover .profile-pic-overlay {
            opacity: 1;
        }

        .profile-pic-upload-icon {
            color: white;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 10px;
            border-radius: 50%;
            font-size: 20px;
            border: 2px solid white;
        }

        .shortcut-link {
            color: inherit;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: .375rem;
            /* Sesuai radius Bootstrap */
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }

        .shortcut-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
            transform: translateY(-3px);
            color: inherit;
        }

        /* Penyesuaian untuk Dark Mode */
        body.dark-mode .shortcut-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .shortcut-link .d-block {
            font-size: 0.8rem;
            margin-top: 5px;
        }
    </style>
</head>

<body class="<?= $theme_class ?>">

    <?php include 'main_nav.php'; ?>


    <main class="container mt-4 d-flex flex-column" style="min-height: calc(95vh - 70px);">
        <?= $message ?>
        <div class="row flex-grow-1">
            <div class="col-md-12 d-flex flex-column mb-4">
                <div class="card shadow-sm flex-grow-1 mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">Latest Announcements <i
                            class="fas fa-bullhorn"></i></div>
                    <div class="card-body">
                        <div id="announcement-list" class="list-group list-group-flush" style="overflow-y: auto;">
                            <div class="text-center p-5">
                                <div class="spinner-border" role="status"><span class="visually-hidden">Load...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">Application Shortcuts</div>
                    <div class="card-body">
                        <div class="grid-container">
                            <div class="shortcut-item">
                                <a href="dashboard.php" class="square-box text-decoration-none">
                                    <i class="fas fa-home fa-3x"></i>
                                </a>
                                <div class="shortcut-label">Dashboard</div>
                            </div>
                            <div class="shortcut-item">
                                <a href="elearning.php" class="square-box text-decoration-none">
                                    <i class="fas fa-book fa-3x"></i>
                                </a>
                                <div class="shortcut-label">E-Learning</div>
                            </div>
                            <div class="shortcut-item">
                                <a href="sop.php" class="square-box text-decoration-none">
                                    <i class="fas fa-chalkboard-user fa-3x"></i>
                                </a>
                                <div class="shortcut-label">Work Instruction</div>
                            </div>
                            <div class="shortcut-item">
                                <a href="calendar.php" class="square-box text-decoration-none">
                                    <i class="fas fa-calendar fa-3x"></i>
                                </a>
                                <div class="shortcut-label">Calendar</div>
                            </div>
                            <div class="shortcut-item">
                                <a href="polls.php" class="square-box text-decoration-none">
                                    <i class="fas fa-poll fa-3x"></i>
                                </a>
                                <div class="shortcut-label">Polls</div>
                            </div>
                            <div class="shortcut-item">
                                <a href="employee_directory.php" class="square-box text-decoration-none">
                                    <i class="fas fa-address-card fa-3x"></i>
                                </a>
                                <div class="shortcut-label">Employee Directory</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">Features Shortcuts</div>
                    <div class="card-body">
                        <div class="grid-container">
                            <?php if (!empty($shortcuts)): ?>
                                <?php foreach ($shortcuts as $shortcut): ?>
                                    <div class="shortcut-item">
                                        <a href="<?= htmlspecialchars($shortcut['url']) ?>" target="_blank"
                                            class="square-box text-decoration-none">
                                            <i class="<?= htmlspecialchars($shortcut['icon_class']) ?> fa-3x"></i>
                                        </a>
                                        <div class="shortcut-label"><?= htmlspecialchars($shortcut['name']) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-4">
                                    <p class="text-muted">No shortcuts available.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">Favorite Documents</div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        <div id="favorite-docs-list" class="list-group list-group-flush">
                            <?php if (!empty($favorite_docs)): ?>
                                <?php foreach ($favorite_docs as $doc): ?>
                                    <a href="sop.php#doc-<?= $doc['id'] ?>"
                                        class="list-group-item list-group-item-action d-flex align-items-center">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                        <span class="text-truncate">
                                            <?= htmlspecialchars($doc['title']) ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center p-3">You don't have any favorite documents yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </main>

    <div class="modal fade" id="uploadProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="upload_profile_picture.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload New Profile Photo</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Select an image file (JPG, PNG, GIF) with a maximum size of 2 MB.</p>
                        <div class="mb-3"><label for="profile_pic_input" class="form-label">Select File</label><input
                                class="form-control" type="file" name="profile_pic" id="profile_pic_input"
                                accept="image/png, image/jpeg, image/gif" required></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button><button type="submit"
                            class="btn btn-primary">Upload</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById("mySidebar").classList.toggle("active");
            document.getElementById("overlay").classList.toggle("active");
        }
        document.addEventListener('DOMContentLoaded', function () {
            const announcementList = document.getElementById('announcement-list');

            function fetchAnnouncements() {
                fetch('api/get_announcements.php')
                    .then(response => response.json())
                    .then(data => {
                        announcementList.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(item => {
                                const listItem = document.createElement('a');
                                listItem.className = 'list-group-item list-group-item-action';

                                const formattedDate = new Date(item.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                                const isUnread = item.is_read === 0;
                                const titleClass = isUnread ? 'unread' : '';
                                const newBadge = isUnread ? '<span class="new-badge">NEW</span>' : '';
                                let icon = '';
                                let title = item.title;

                                if (item.type === 'announcement') {
                                    listItem.href = `view_announcement.php?id=${item.id}`;
                                    icon = item.is_pinned == 1 ? '<i class="fas fa-thumbtack text-warning me-2" title="Disematkan"></i>' : '<i class="fas fa-bullhorn text-primary me-2"></i>';
                                } else if (item.type === 'event') {
                                    listItem.href = item.link || '#';
                                    icon = '<i class="fas fa-calendar-alt text-success me-2"></i>';
                                } else if (item.type === 'poll') {
                                    listItem.href = item.link || '#';
                                    icon = '<i class="fas fa-poll text-info me-2"></i>';
                                } else {
                                    listItem.href = item.link || '#';
                                    icon = '<i class="fas fa-file-alt text-dark me-2"></i>';
                                }

                                listItem.innerHTML = `
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${icon}${title}</h6>
                                    <small class="text-muted">${formattedDate}</small>
                                </div>
                            `;
                                announcementList.appendChild(listItem);
                            });
                        } else {
                            announcementList.innerHTML = '<p class="text-center text-muted p-5">Tidak ada aktivitas terbaru.</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching feed:', error);
                        announcementList.innerHTML = '<p class="text-center text-danger p-5">Gagal memuat aktivitas.</p>';
                    });
            }

            fetchAnnouncements();
        });
    </script>
</body>

</html>