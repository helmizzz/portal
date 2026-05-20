<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Cek hak akses
$permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('manage_polls', $permissions)) {
    header("Location: ../dashboard.php");
    exit();
}

$poll_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($poll_id === 0) {
    header("Location: manage_polls.php");
    exit();
}

// Ambil data polling
$stmt_poll = $conn->prepare("SELECT title, description FROM polls WHERE id = ?");
$stmt_poll->bind_param("i", $poll_id);
$stmt_poll->execute();
$poll = $stmt_poll->get_result()->fetch_assoc();
if (!$poll) {
    die("Survei tidak ditemukan.");
}

// Ambil semua pertanyaan dan hasilnya
$questions_with_results = [];
$stmt_q = $conn->prepare("SELECT id, question_text FROM poll_questions WHERE poll_id = ? ORDER BY sort_order ASC");
$stmt_q->bind_param("i", $poll_id);
$stmt_q->execute();
$result_q = $stmt_q->get_result();
while ($question = $result_q->fetch_assoc()) {
    $sql_results = "SELECT po.option_text, COUNT(pr.id) as vote_count
                    FROM poll_options po
                    LEFT JOIN poll_responses pr ON po.id = pr.option_id
                    WHERE po.question_id = ?
                    GROUP BY po.id
                    ORDER BY po.id";
    $stmt_results = $conn->prepare($sql_results);
    $stmt_results->bind_param("i", $question['id']);
    $stmt_results->execute();
    $question['results'] = $stmt_results->get_result()->fetch_all(MYSQLI_ASSOC);
    $question['total_votes'] = array_sum(array_column($question['results'], 'vote_count'));
    $questions_with_results[] = $question;
}

?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Survey Results</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="../assets/js/theme.js" defer></script>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">Survey Results: <?= htmlspecialchars($poll['title']) ?></h2>
            <a href="manage_polls.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back to Management</a>
        </div>

        <?php foreach ($questions_with_results as $question): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><?= htmlspecialchars($question['question_text']) ?></h5>
                        <small class="text-muted">Total Respondents: <?= $question['total_votes'] ?></small>
                    </div>
                    <div class="card-body">
                        <?php if ($question['total_votes'] > 0): ?>
                                <?php foreach ($question['results'] as $result): ?>
                                        <?php $percentage = round(($result['vote_count'] / $question['total_votes']) * 100, 1); ?>
                                        <div class="mb-3">
                                            <strong><?= htmlspecialchars($result['option_text']) ?></strong>
                                            <span class="float-end"><?= $result['vote_count'] ?> Votes (<?= $percentage ?>%)</span>
                                            <div class="progress mt-1" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" style="width: <?= $percentage ?>%;"
                                                    aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                                    <?= $percentage ?>%
                                                </div>
                                            </div>
                                        </div>
                                <?php endforeach; ?>
                        <?php else: ?>
                                <p class="text-center">There are no votes yet for this question..</p>
                        <?php endif; ?>
                    </div>
                </div>
        <?php endforeach; ?>
    </div>
</body>

</html>