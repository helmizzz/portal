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
//tambahan tahun


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
    $sql_tahun = "SELECT DISTINCT t.id_tahun, t.tahun 
                FROM tahun t 
                JOIN documents d ON d.tahun = t.tahun 
                ORDER BY t.tahun DESC";
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

$tahun_list = [];
$sql_tahun = "SELECT id_tahun, tahun FROM tahun ORDER BY tahun DESC";
$result_tahun = $conn->query($sql_tahun);
while ($row = $result_tahun->fetch_assoc()) {
    $tahun_list[] = $row;
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
                        <select id="tahun-filter-select" class="form-select form-select-sm mt-2" title="Filter Tahun">
                            <option value="">All Years</option>
                            <?php foreach ($tahun_list as $t): ?>
                                <option value="<?= $t['id_tahun'] ?>"><?= htmlspecialchars($t['tahun']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="list-group list-group-flush flex-grow-1 overflow-auto" id="document-tree">
                    </div>

                </div>
            </div>
            <div class="col-md-8 col-lg-9 d-flex flex-column h-100">
                <!-- Deskripsi Dokumen -->
                <div class="card shadow-sm mb-2">
                    <div class="card-body">
                        <p style="font-weight: ; font-size: 15px;margin-top: -5px;margin-bottom: 0px;" id="document-title">Pilih dokumen dari kiri untuk melihat detailnya</p>
                        <p style="font-weight: bold; font-size: 12x;margin-top: ;margin-bottom: 0px;" id="document-kode-file" class="text-muted">Akan muncul kode file</p>
                        <p style="font-size: 12px;margin-top: ;margin-bottom: 0px;">
                            | Tanggal Upload: <span id="document-upload-date">-</span>
                            | Terakhir Update: <span id="document-update-date">-</span>
                            | Upload by: <span id="document-upload-by">-</span>
                        </p>

                    </div>
                </div>

                <!-- PDF Viewer -->
                <div class="card shadow-sm col-lg">
                    <div class="card-body p-0 d-flex justify-content-center align-items-center h-100">
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
                                <div id="no-document-selected" class="text-muted text-center">Select a document from the
                                    menu on the left.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const documentTree = document.getElementById('document-tree');
            const searchInput = document.getElementById('document-search-input');
            const dateFilterInput = document.getElementById('date-filter-input');
            const departementFilterSelect = document.getElementById('departement-filter-select');

            if (!documentTree) {
                console.error("Elemen dengan ID 'document-tree' tidak ditemukan.");
                return;
            }

            const pdfFrame = document.getElementById('pdf-frame');
            const noDocumentSelected = document.getElementById('no-document-selected');
            let activeDocument = null;
            let activeDocumentName = '';

            function logUserAction(actionType, documentName) {
                fetch('api/log_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action_type: actionType, document_name: documentName }),
                }).catch(error => console.error('Gagal mengirim log:', error));
            }

            function fetchTreeviewData(query = '', date_filter = '', dept_filter = '', year_filter = '') {
                const params = new URLSearchParams();
                if (query) params.append('q', query);
                if (date_filter) params.append('date', date_filter);
                if (dept_filter) params.append('dept', dept_filter);
                if (year_filter) params.append('year', year_filter);
                fetch(`api/get_tree.php?${params.toString()}`).then(response => response.json()).then(data => { renderTree(data); if (query.length > 0 || date_filter.length > 0 || dept_filter.length > 0 || year_filter.length > 0) { documentTree.querySelectorAll('.folder-item > ul').forEach(ul => { ul.style.display = 'block'; ul.parentNode.classList.add('expanded'); }); } }).catch(error => console.error('Ada masalah saat mengambil data treeview:', error));
            }

            function renderTree(data) {
                documentTree.innerHTML = '';
                if (data && data.length > 0) {
                    buildTree(data, documentTree);
                } else {
                    documentTree.innerHTML = '<li class="p-3 text-muted">Tidak ada dokumen yang sesuai.</li>';
                }
            }

            function buildTree(items, parentElement) {
                items.forEach(item => {
                    const li = document.createElement('li');
                    li.id = item.id;

                    if (item.type === 'folder') {
                        li.classList.add('folder-item', 'list-group-item', 'border-0');
                        li.innerHTML = `<div class="folder-name-container"><span class="folder-name">${item.name}</span></div>`;
                        const ul = document.createElement('ul');
                        ul.classList.add('list-group', 'list-group-flush', 'ps-3');
                        ul.style.display = 'none';
                        li.appendChild(ul);
                        if (item.children && item.children.length > 0) {
                            buildTree(item.children, ul);
                        }
                    } else if (item.type === 'document') {
                        li.classList.add('document-item', 'list-group-item', 'list-group-item-action', 'border-0', 'd-flex', 'justify-content-between', 'align-items-center');
                        const favoriteIconClass = item.is_favorite ? 'fas' : 'far';
                        const favoriteIconColor = item.is_favorite ? 'text-warning' : '';
                        li.innerHTML = `<span class="document-name text-truncate">${item.name}</span> <i class="${favoriteIconClass} fa-star favorite-icon ${favoriteIconColor}" data-doc-id="${item.doc_id}"></i>`;
                        li.setAttribute('data-file', item.file_name);
                        li.setAttribute('data-doc-name', item.name);
                        li.setAttribute('data-upload-at', item.upload_at || '-');
                        li.setAttribute('data-update-at', item.update_at || '-');
                        li.setAttribute('data-upload-by', item.upload_by || '-');
                        li.setAttribute('data-kode-file', item.kode_file || '-');
                        li.setAttribute('data-folder', item.nama_dept || '');
                        li.setAttribute('data-tahun', item.tahun || '');
                    }
                    parentElement.appendChild(li);
                });
            }

            function applyFilters() {
                const query = searchInput.value.trim();
                const dateFilter = dateFilterInput.value;
                const deptFilter = departementFilterSelect.value;
                const yearFilter = document.getElementById('tahun-filter-select').value;
                fetchTreeviewData(query, dateFilter, deptFilter, yearFilter);
            }

            searchInput.addEventListener('input', applyFilters);
            dateFilterInput.addEventListener('change', applyFilters);
            departementFilterSelect.addEventListener('change', applyFilters);
            document.getElementById('tahun-filter-select').addEventListener('change', applyFilters);

            fetchTreeviewData();

            documentTree.addEventListener('click', function (event) {
                const target = event.target;
                const clickedItem = target.closest('li');
                if (!clickedItem) return;

                // Logika untuk favorit
                if (target.classList.contains('favorite-icon')) {
                    const docId = target.dataset.docId;
                    target.classList.toggle('far');
                    target.classList.toggle('fas');
                    target.classList.toggle('text-warning');
                    fetch('api/toggle_favorite.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ document_id: docId })
                    }).catch(error => console.error('Gagal memfavoritkan:', error));
                    return; // Hentikan propagasi agar tidak membuka dokumen
                }

                if (clickedItem.classList.contains('folder-item')) {
                    const ul = clickedItem.querySelector('ul');
                    if (ul) {
                        ul.style.display = ul.style.display === 'none' ? 'block' : 'none';
                        clickedItem.classList.toggle('expanded');
                    }
                } else if (clickedItem.classList.contains('document-item')) {
                    if (activeDocument) {
                        activeDocument.classList.remove('active');
                    }
                    clickedItem.classList.add('active');
                    activeDocument = clickedItem;

                    const fileName = clickedItem.getAttribute('data-file');
                    const folderName = clickedItem.getAttribute('data-folder');
                    const tahun = clickedItem.getAttribute('data-tahun');
                    activeDocumentName = clickedItem.getAttribute('data-doc-name');
                    if (fileName) {
                        const origin = window.location.origin;
                        const pdfUrl = `../../../uploads/${encodeURIComponent(tahun)}/${encodeURIComponent(folderName)}/${encodeURIComponent(fileName)}`;
                        loadPDF(pdfUrl);
                        logUserAction('view_document', activeDocumentName);

                        // Update metadata
                        document.getElementById('document-title').textContent = activeDocumentName;
                        document.getElementById('document-upload-date').textContent = clickedItem.getAttribute('data-upload-at');
                        document.getElementById('document-update-date').textContent = clickedItem.getAttribute('data-update-at');
                        document.getElementById('document-upload-by').textContent = clickedItem.getAttribute('data-upload-by');
                        document.getElementById('document-kode-file').textContent = "Kode File : " + clickedItem.getAttribute('data-kode-file');

                    }
                }
            });

            function loadPDF(url) {
                if (noDocumentSelected.style.display !== 'none') {
                    noDocumentSelected.style.display = 'none';
                }
                pdfFrame.style.display = 'block';

                const viewerUrl = `assets/pdfjs/web/viewer.html?file=${url}`;

                if (pdfFrame.src !== viewerUrl) {
                    pdfFrame.src = viewerUrl;
                }
            }

            // Function: Disable Copy/Paste/Right-Click/Shortcuts
            function disableActions(targetDoc) {
                if (!targetDoc) return;

                // Block Context Menu
                targetDoc.addEventListener('contextmenu', event => {
                    event.preventDefault();
                    // Optional: logUserAction('attempt_right_click', activeDocumentName);
                });

                // Block Copy, Cut, Paste
                ['copy', 'cut', 'paste'].forEach(evt => {
                    targetDoc.addEventListener(evt, event => {
                        event.preventDefault();
                    });
                });

                // Block Shortcuts
                targetDoc.addEventListener('keydown', event => {
                    const isCtrlOrMeta = event.ctrlKey || event.metaKey;
                    const key = event.key.toLowerCase();

                    // Block PrintScreen
                    if (event.key === 'PrintScreen' || event.keyCode === 44) {
                        event.preventDefault();
                        alert("Aksi screenshot tidak diizinkan.");
                    }

                    // Block Ctrl+P (Print), Ctrl+S (Save), Ctrl+C/X/V (Copy/Cut/Paste), Ctrl+A (Select All), Ctrl+U (View Source)
                    if (isCtrlOrMeta && ['p', 's', 'c', 'x', 'v', 'a', 'u'].includes(key)) {
                        event.preventDefault();
                        alert("Kamu tidak di izinkan");
                        // alert("Aksi ini tidak diizinkan."); // Optional: uncomment if too annoying
                    }

                    // Block DevTools (Ctrl+Shift+I)
                    if (isCtrlOrMeta && event.shiftKey && key === 'i') {
                        event.preventDefault();
                        alert("Kamu tidak di izinkan");
                    }
                });
            }

            // Apply to Main Document
            disableActions(document);

            // Apply to PDF Iframe when loaded
            pdfFrame.addEventListener('load', function () {
                try {
                    const iframeDoc = pdfFrame.contentDocument || pdfFrame.contentWindow.document;
                    disableActions(iframeDoc);

                    // Re-apply on internal navigation (if any)
                    // Note: PDF.js might override some things, but this catches bubble-up events.
                } catch (e) {
                    console.warn("Cannot access iframe content (Cross-Origin?):", e);
                }
            });
        });
    </script>

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