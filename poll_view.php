<?php
require_once 'includes/init.php';
session_start();
require_once 'includes/db_connect.php';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("Invalid or incomplete survey link.");
}

// Ambil data survei berdasarkan TOKEN
$stmt_poll = $conn->prepare("SELECT id, title, description, status FROM polls WHERE share_token = ?");
$stmt_poll->bind_param("s", $token);
$stmt_poll->execute();
$poll = $stmt_poll->get_result()->fetch_assoc();

if (!$poll) {
    die("Survey not found or link is invalid.");
}

$poll_id = $poll['id'];
$is_logged_in = isset($_SESSION['user_id']);

// Cek status polling
if ($poll['status'] === 'closed') {
    $message = "<div class='alert alert-warning'>Sorry, this survey period has closed.</div>";
}

// Ambil semua pertanyaan dan opsinya
$questions = [];
if ($poll) {
    $stmt_q = $conn->prepare("SELECT id, question_text FROM poll_questions WHERE poll_id = ? ORDER BY sort_order ASC");
    $stmt_q->bind_param("i", $poll_id);
    $stmt_q->execute();
    $result_q = $stmt_q->get_result();
    while ($row_q = $result_q->fetch_assoc()) {
        $stmt_o = $conn->prepare("SELECT id, option_text FROM poll_options WHERE question_id = ?");
        $stmt_o->bind_param("i", $row_q['id']);
        $stmt_o->execute();
        $row_q['options'] = $stmt_o->get_result()->fetch_all(MYSQLI_ASSOC);
        $questions[] = $row_q;
    }
}

// Logika saat submit vote
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $poll['status'] === 'active') {
    $votes = $_POST['votes'] ?? [];
    $user_id = $is_logged_in ? $_SESSION['user_id'] : null;
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $voted_question_count = 0;

    $conn->begin_transaction();
    try {
        $sql_insert = "INSERT INTO poll_responses (poll_id, question_id, option_id, user_id, ip_address) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        foreach ($votes as $question_id => $option_id) {
            $question_id = (int)$question_id;
            $option_id = (int)$option_id;

            $sql_check = "SELECT id FROM poll_responses WHERE poll_id = ? AND question_id = ? AND ip_address = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("iis", $poll_id, $question_id, $ip_address);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                continue;
            }

            $stmt_insert->bind_param("iiiis", $poll_id, $question_id, $option_id, $user_id, $ip_address);
            $stmt_insert->execute();
            $voted_question_count++;
        }
        $conn->commit();
        if ($voted_question_count > 0) {
            $message = "<div class='alert alert-success'>Thank you! Your answer has been successfully recorded..</div>";
        } else {
            $message = "<div class='alert alert-warning'>You've probably already filled out this survey..</div>";
        }
    } catch(Exception $e) {
        $conn->rollback();
        $message = "<div class='alert alert-danger'>An error occurred while saving your answer.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="uploads/EIP.png">
    <title>Ikuti Survei</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; }
        .poll-container { max-width: 700px; margin: 40px auto; }
    </style>
</head>
<body>
    <div class="container poll-container">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h3 class="card-title mb-0"><?= htmlspecialchars($poll['title']) ?></h3>
            </div>
            <div class="card-body">
                <?php if (!empty($poll['description'])): ?>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($poll['description'])) ?></p>
                    <hr>
                <?php endif; ?>

                <?php if (isset($message)): ?>
                    <?= $message ?>
                <?php elseif ($poll['status'] === 'active'): ?>
                    <form action="poll_view.php?token=<?= htmlspecialchars($token) ?>" method="POST">
                        <?php foreach ($questions as $index => $question): ?>
                        <div class="mb-4">
                            <h6><?= ($index + 1) . '. ' . htmlspecialchars($question['question_text']) ?></h6>
                            <?php foreach ($question['options'] as $option): ?>
                            <div class="form-check ms-3">
                                <input class="form-check-input" type="radio" name="votes[<?= $question['id'] ?>]" id="option-<?= $option['id'] ?>" value="<?= $option['id'] ?>" required>
                                <label class="form-check-label" for="option-<?= $option['id'] ?>">
                                    <?= htmlspecialchars($option['option_text']) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Submit Answer</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <?php if ($is_logged_in): ?>
            <div class="card-footer text-center">
                <a href="polls.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Survey List</a>
            </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-3">
            <small class="text-muted">Powered by Information Portal</small>
        </div>
    </div>
</body>
</html>