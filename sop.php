<?php
require_once 'includes/init.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Variabel $user_permissions diperlukan oleh main_nav.php
$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);
$can_manage_documents = in_array('manage_documents', $user_permissions);
// tambahan
$user_role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'];

$departements = [];
// dihapus $sql_dept
// dihapus $result_
// tambahan 17/01
if ($user_role === 'Staff') {
    // Jika Staff, hanya ambil departemen milik user tersebut
    $sql_dept = "SELECT d.id, d.name 
                 FROM departements d
                 JOIN users u ON u.departement_id = d.id
                 WHERE u.id = ?";
    $stmt_dept = $conn->prepare($sql_dept);
    $stmt_dept->bind_param("i", $user_id);
    $stmt_dept->execute();
    $result_dept = $stmt_dept->get_result();
} else {
    // Jika Admin atau Manager, ambil semua departemen
    $sql_dept = "SELECT id, name FROM departements";
    $result_dept = $conn->query($sql_dept);
}
// tambaha
while ($row = $result_dept->fetch_assoc()) {
    $departements[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SOP & Work Instructions</title>
    <link rel="icon" type="image/png" href="uploads/EIP.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="<?= $_SESSION['theme'] ?? 'light' ?>-mode">

    <?php include 'main_nav.php'; ?>

    <main class="main-container container-fluid d-flex flex-grow-1 overflow-hidden">
        <div class="row w-100 h-100">
            <div class="col-md-4 col-lg-3 d-flex flex-column h-100">
                <div class="card p-3 shadow-sm side-panel flex-grow-1">
                    <h5 class="card-title mb-3">Document</h5>
                    <div class="mb-3">
                        <input type="text" id="document-search-input" class="form-control form-control-sm mb-2"
                            placeholder="Search for documents...">
                        <input type="date" id="date-filter-input" class="form-control form-control-sm mb-2"
                            title="Upload date filter">
                        <select id="departement-filter-select" class="form-select form-select-sm"
                            title="Filter departement">
                            <option value="">All Departments</option>
                            <option value="public">Public</option>
                            <?php foreach ($departements as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="list-group list-group-flush flex-grow-1 overflow-auto" id="document-tree">
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-lg-9 d-flex flex-column h-100">
                <script>
                    var noPrint = false;
                    var noCopy = false;
                    var noScreenshot = true;
                    var autoBlur = true;
                </script>
                <div class="card shadow-sm pdf-viewer-panel flex-grow-1">
                    <div class="card-body p-0 d-flex justify-content-center align-items-center h-100">
                        <iframe id="pdf-frame" src="" frameborder="0" class="w-100 h-100"
                            style="display:none;"></iframe>
                        <!-- Pemanggil dokumen -->
                        <div id="no-document-selected" class="text-muted text-center">Select a document from the menu on
                            the left.</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>

    <script src="assets/noprintjs/noprint.js"></script>

    <script>
        // <<< MANTRA BARU UNTUK MEMBUKA DOKUMEN OTOMATIS >>>
        document.addEventListener('DOMContentLoaded', function () {
            const openDocumentFromHash = () => {
                if (window.location.hash) {
                    const docId = window.location.hash.substring(1); // Hapus '#' -> menjadi 'doc-123'
                    // Kita tunggu sebentar untuk memastikan tree sudah di-render oleh script.js
                    const checkExist = setInterval(function () {
                        const docElement = document.getElementById(docId);
                        if (docElement) {
                            clearInterval(checkExist);
                            // Gulir ke elemen dan klik
                            docElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            // Klik pada nama dokumen, bukan ikon favorit
                            const docNameSpan = docElement.querySelector('.document-name');
                            if (docNameSpan) {
                                docNameSpan.click();
                            } else {
                                docElement.click(); // fallback
                            }
                        }
                    }, 100); // Cek setiap 100ms
                }
            };

            // Panggil fungsi ini setelah data treeview dimuat.
            // Kita modifikasi script.js untuk memanggil ini, atau cara mudahnya, panggil di sini
            // dengan asumsi tree akan dimuat dalam beberapa saat.
            openDocumentFromHash();

            // Untuk notifikasi bell
            const bellIcon = document.getElementById('notification-bell-icon');
            const notificationCount = document.getElementById('notification-count');
            const notificationsContainer = document.getElementById('notifications-container');
            const notificationList = document.getElementById('notification-list');

            function fetchNotifications() {
                fetch('api/notifications.php?action=fetch')
                    .then(response => response.json())
                    .then(data => {
                        updateNotificationUI(data.notifications, data.unread_count);
                    })
                    .catch(error => console.error('Error fetching notifications:', error));
            }

            function updateNotificationUI(notifications, unreadCount) {
                if (unreadCount > 0) {
                    notificationCount.textContent = unreadCount;
                    notificationCount.style.display = 'block';
                } else {
                    notificationCount.style.display = 'none';
                }

                notificationList.innerHTML = '';
                if (notifications.length > 0) {
                    notifications.forEach(notif => {
                        const item = document.createElement('a');
                        item.href = notif.link || '#';
                        item.classList.add('notification-item');
                        if (notif.is_read == 0) {
                            item.classList.add('unread');
                        }

                        const message = document.createElement('div');
                        message.textContent = notif.message;

                        const time = document.createElement('small');
                        time.textContent = new Date(notif.created_at).toLocaleString('id-ID');

                        item.appendChild(message);
                        item.appendChild(time);
                        notificationList.appendChild(item);
                    });
                } else {
                    notificationList.innerHTML = '<div class="no-notification">Tidak ada notifikasi.</div>';
                }
            }

            bellIcon.addEventListener('click', function (e) {
                e.stopPropagation();
                const isShown = notificationsContainer.style.display === 'block';
                notificationsContainer.style.display = isShown ? 'none' : 'block';

                if (!isShown && notificationCount.style.display === 'block') {
                    // Mark notifications as read
                    fetch('api/notifications.php?action=mark_read', { method: 'POST' })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                notificationCount.style.display = 'none';
                                document.querySelectorAll('.notification-item.unread').forEach(item => {
                                    item.classList.remove('unread');
                                });
                            }
                        });
                }
            });


            // Close dropdown if clicked outside
            document.addEventListener('click', function (e) {
                if (notificationsContainer && !notificationsContainer.contains(e.target) && bellIcon && !bellIcon.contains(e.target)) {
                    notificationsContainer.style.display = 'none';
                }
            });

            fetchNotifications();
            setInterval(fetchNotifications, 5000);
        });

    </script>
</body>

</html>