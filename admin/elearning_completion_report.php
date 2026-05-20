<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('view_elearning_report', $permissions)) {
    header("Location: ../dashboard.php");
    exit();
}

$courses = $conn->query("SELECT id, title FROM elearning_courses ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);

$selected_course_id = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;
$completers = [];
$non_completers = [];
$course_title = '';

if ($selected_course_id > 0) {
    $course_title = $conn->query("SELECT title FROM elearning_courses WHERE id = $selected_course_id")->fetch_assoc()['title'];

    // Get total materials for the course
    $total_materials_result = $conn->query("SELECT COUNT(*) as total FROM elearning_materials WHERE course_id = $selected_course_id");
    $total_materials = $total_materials_result ? $total_materials_result->fetch_assoc()['total'] : 0;

    if ($total_materials > 0) {
        // Get all active users
        $all_users_result = $conn->query("SELECT id, username FROM users WHERE is_active = 1");
        $all_users = [];
        while ($user = $all_users_result->fetch_assoc()) {
            $all_users[$user['id']] = $user['username'];
        }

        // Get completion data
        $sql_completion = "SELECT ec.user_id
                           FROM elearning_completion ec
                           JOIN elearning_materials em ON ec.material_id = em.id
                           WHERE em.course_id = ?
                           GROUP BY ec.user_id
                           HAVING COUNT(DISTINCT ec.material_id) = ?";
        $stmt_completion = $conn->prepare($sql_completion);
        $stmt_completion->bind_param("ii", $selected_course_id, $total_materials);
        $stmt_completion->execute();
        $result_completion = $stmt_completion->get_result();

        $completed_user_ids = [];
        while ($row = $result_completion->fetch_assoc()) {
            $user_id = $row['user_id'];
            if (isset($all_users[$user_id])) {
                $completers[] = ['user_id' => $user_id, 'username' => $all_users[$user_id]];
                $completed_user_ids[] = $user_id;
            }
        }

        foreach ($all_users as $user_id => $username) {
            if (!in_array($user_id, $completed_user_ids)) {
                $non_completers[] = ['user_id' => $user_id, 'username' => $username];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>E-Learning Completion Report</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="../assets/js/theme.js" defer></script>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <h2><i class="fas fa-user-check me-2"></i>E-Learning Completion Report</h2>
        <p class="text-muted">Select a course to view the report on this page, or export it directly to get the full
            report.</p>

        <div class="card mb-4">
            <div class="card-header">Select a Course</div>
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="course_id" class="form-label">Course</label>
                        <select name="course_id" id="course_id" class="form-select">
                            <option value="">-- Select a Course --</option>
                            <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>" <?= ($selected_course_id == $course['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($course['title']) ?>
                                    </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex">
                        <button type="submit" class="btn btn-primary flex-grow-1 me-2">Generate Report</button>
                        <a href="export_elearning_completion.php?item=all" id="export-btn"
                            class="btn btn-success flex-grow-1" title="Ekspor ke Excel">
                            <i class="fas fa-file-excel"></i> <span id="export-btn-text">Export All</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($selected_course_id)): ?>
                <h3 class="mt-4 mb-3">Report for: <span class="text-primary"><?= htmlspecialchars($course_title) ?></span></h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-check-circle me-2"></i> Has Completed (<?= count($completers) ?> Users)
                            </div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($completers) > 0):
                                            foreach ($completers as $user): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                                        </tr>
                                                <?php endforeach; else: ?>
                                                <tr>
                                                    <td class="text-center">No one has completed this course yet.</td>
                                                </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-danger text-white">
                                <i class="fas fa-times-circle me-2"></i> Has Not Completed (<?= count($non_completers) ?> Users)
                            </div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($non_completers) > 0):
                                            foreach ($non_completers as $user): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                                        </tr>
                                                <?php endforeach; else: ?>
                                                <tr>
                                                    <td class="text-center">All active users have completed this course.</td>
                                                </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
        <?php endif; ?>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const exportBtn = document.getElementById('export-btn');
            const exportBtnText = document.getElementById('export-btn-text');
            const courseSelect = document.getElementById('course_id');

            function updateExportLink() {
                const selectedValue = courseSelect.value;
                if (selectedValue) {
                    exportBtn.href = `export_elearning_completion.php?item=course-${selectedValue}`;
                    exportBtnText.textContent = 'Export Selected';
                } else {
                    exportBtn.href = 'export_elearning_completion.php?item=all';
                    exportBtnText.textContent = 'Export All';
                }
            }

            updateExportLink();
            courseSelect.addEventListener('change', updateExportLink);
        });
    </script>
</body>

</html>