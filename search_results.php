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
$user_permissions = getUserPermissions($conn, $user_id);

$search_query = $_GET['q'] ?? '';
$results = [
    'documents' => [],
    'announcements' => [],
    'users' => []
];

if (!empty($search_query)) {
    $search_param = "%" . $search_query . "%";

    // 1. Cari Dokumen (SOP & Work Instructions)
    $sql_docs = "SELECT id, title FROM documents WHERE title LIKE ? ORDER BY title ASC";
    $stmt_docs = $conn->prepare($sql_docs);
    $stmt_docs->bind_param("s", $search_param);
    $stmt_docs->execute();
    $result_docs = $stmt_docs->get_result();
    while ($row = $result_docs->fetch_assoc()) {
        $results['documents'][] = $row;
    }

    // 2. Cari Pengumuman
    $current_time = date('Y-m-d H:i:s');
    $sql_ann = "SELECT id, title FROM announcements WHERE (title LIKE ? OR content LIKE ?) AND (start_date IS NULL OR start_date <= ?) AND (end_date IS NULL OR end_date >= ?) ORDER BY created_at DESC";
    $stmt_ann = $conn->prepare($sql_ann);
    $stmt_ann->bind_param("ssss", $search_param, $search_param, $current_time, $current_time);
    $stmt_ann->execute();
    $result_ann = $stmt_ann->get_result();
    while ($row = $result_ann->fetch_assoc()) {
        $results['announcements'][] = $row;
    }

    // 3. Cari Pengguna (hanya untuk Admin)
    if (in_array('manage_users', $user_permissions)) {
        $sql_users = "SELECT id, username FROM users WHERE username LIKE ? ORDER BY username ASC";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("s", $search_param);
        $stmt_users->execute();
        $result_users = $stmt_users->get_result();
        while ($row = $result_users->fetch_assoc()) {
            $results['users'][] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="icon" type="image/png" href="uploads/EIP.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= $theme_class ?>">
    
    <?php include 'main_nav.php'; ?>

    <main class="container mt-4">
        <h2 class="mb-4">Search results for: <span class="text-primary"><?= htmlspecialchars($search_query) ?></span></h2>

        <?php if (empty($search_query)): ?>
            <div class="alert alert-warning">Please enter a search term.</div>
        <?php elseif (empty($results['documents']) && empty($results['announcements']) && empty($results['users'])): ?>
            <div class="alert alert-info text-center">
                <h4><i class="fas fa-search"></i> No results found</h4>
                <p>Your search for "<?= htmlspecialchars($search_query) ?>" did not match any content.</p>
            </div>
        <?php else: ?>

            <?php if (!empty($results['documents'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-book me-2"></i>Documents (SOP & Work Instructions)</h5></div>
                <div class="list-group list-group-flush">
                    <?php foreach ($results['documents'] as $doc): ?>
                        <a href="sop.php#doc-<?= $doc['id'] ?>" class="list-group-item list-group-item-action">
                           <i class="fas fa-file-pdf text-danger me-2"></i> <?= htmlspecialchars($doc['title']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($results['announcements'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Announcements</h5></div>
                <div class="list-group list-group-flush">
                    <?php foreach ($results['announcements'] as $ann): ?>
                        <a href="view_announcement.php?id=<?= $ann['id'] ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-info-circle text-primary me-2"></i> <?= htmlspecialchars($ann['title']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($results['users'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header"><h5 class="mb-0"><i class="fas fa-users me-2"></i>Users</h5></div>
                <div class="list-group list-group-flush">
                    <?php foreach ($results['users'] as $user): ?>
                        <a href="admin/manage_users.php?q=<?= urlencode($user['username']) ?>" class="list-group-item list-group-item-action">
                           <i class="fas fa-user text-secondary me-2"></i> <?= htmlspecialchars($user['username']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </main>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>