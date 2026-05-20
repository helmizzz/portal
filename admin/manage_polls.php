<?php
require_once '../includes/theme_handler.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Cek autentikasi dan hak akses
$message = '';
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$user_permissions = getUserPermissions($conn, $_SESSION['user_id']);
if (!in_array('manage_polls', $user_permissions) && $_SESSION['role'] !== 'Admin') {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }
    header("Location: ../dashboard.php"); // Atau kembali ke dashboard jika login tapi tidak punya akses
    exit();
}

$admin_id = $_SESSION['user_id'];

// Logika untuk Aksi (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_poll') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $questions = $_POST['questions'] ?? [];

        if (!empty($title) && count($questions) > 0) {
            $conn->begin_transaction();
            try {
                // Insert ke tabel polls
                $sql_poll = "INSERT INTO polls (title, description, created_by) VALUES (?, ?, ?)";
                $stmt_poll = $conn->prepare($sql_poll);
                $stmt_poll->bind_param("ssi", $title, $description, $admin_id);
                $stmt_poll->execute();
                $poll_id = $conn->insert_id;

                $sql_question = "INSERT INTO poll_questions (poll_id, question_text, sort_order) VALUES (?, ?, ?)";
                $stmt_question = $conn->prepare($sql_question);

                $sql_option = "INSERT INTO poll_options (question_id, option_text) VALUES (?, ?)";
                $stmt_option = $conn->prepare($sql_option);

                $question_order = 0;
                foreach ($questions as $question_data) {
                    $question_text = trim($question_data['text']);
                    $options = $question_data['options'] ?? [];

                    if (!empty($question_text) && count($options) >= 2) {
                        // Insert pertanyaan
                        $stmt_question->bind_param("isi", $poll_id, $question_text, $question_order);
                        $stmt_question->execute();
                        $question_id = $conn->insert_id;
                        $question_order++;

                        // Insert opsi
                        foreach ($options as $option_text) {
                            if (!empty(trim($option_text))) {
                                $stmt_option->bind_param("is", $question_id, $option_text);
                                $stmt_option->execute();
                            }
                        }
                    }
                }

                $conn->commit();
                $_SESSION['flash_message'] = "<div class='alert alert-success'>Survei berhasil dibuat.</div>";
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['flash_message'] = "<div class='alert alert-danger'>Gagal membuat survei: " . $e->getMessage() . "</div>";
            }
        } else {
            $_SESSION['flash_message'] = "<div class='alert alert-warning'>Judul dan minimal 1 pertanyaan dengan 2 opsi harus diisi.</div>";
        }
    }
    header("Location: manage_polls.php");
    exit();
}

// Logika untuk Aksi (GET)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    if ($_GET['action'] === 'delete') {
        $sql = "DELETE FROM polls WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Survei berhasil dihapus.</div>";
        }
    } elseif ($_GET['action'] === 'toggle_status') {
        $sql_current = "SELECT status FROM polls WHERE id = ?";
        $stmt_current = $conn->prepare($sql_current);
        $stmt_current->bind_param("i", $id);
        $stmt_current->execute();
        $current_status = $stmt_current->get_result()->fetch_assoc()['status'];

        $new_status = ($current_status === 'active') ? 'closed' : 'active';

        $sql_toggle = "UPDATE polls SET status = ? WHERE id = ?";
        $stmt_toggle = $conn->prepare($sql_toggle);
        $stmt_toggle->bind_param("si", $new_status, $id);
        if ($stmt_toggle->execute()) {
            $_SESSION['flash_message'] = "<div class='alert alert-success'>Status survei berhasil diubah.</div>";
        }
    } elseif ($_GET['action'] === 'share') {
        $stmt_check = $conn->prepare("SELECT share_token FROM polls WHERE id = ?");
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $token = $stmt_check->get_result()->fetch_assoc()['share_token'];

        if (empty($token)) {
            $token = bin2hex(random_bytes(16));
            $stmt_update = $conn->prepare("UPDATE polls SET share_token = ? WHERE id = ?");
            $stmt_update->bind_param("si", $token, $id);
            $stmt_update->execute();
        }

        $link_scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $link_host = $_SERVER['HTTP_HOST'];
        $link_path = dirname($_SERVER['PHP_SELF'], 2) . "/poll_view.php?token=" . $token;
        $full_link = "{$link_scheme}://{$link_host}{$link_path}";

        $_SESSION['flash_message'] = "<div class='alert alert-success'>Public link successfully created. Copy the link below:<input type='text' class='form-control mt-2' value='" . htmlspecialchars($full_link) . "' readonly onclick='this.select()'></div>";
    }
    header("Location: manage_polls.php");
    exit();
}

// Ambil semua data polling
$polls = [];
$sql = "SELECT p.id, p.title, p.status, p.created_at, u.username, 
        (SELECT COUNT(DISTINCT ip_address, user_id) FROM poll_responses WHERE poll_id = p.id) as response_count
        FROM polls p 
        LEFT JOIN users u ON p.created_by = u.id 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $polls[] = $row;
}

?>
<!DOCTYPE html>
<html lang="id" data-api-path="../">

<head>
    <meta charset="UTF-8">
    <title>Survey Management</title>
    <link rel="icon" type="image/png" href="../uploads/EIP.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="../assets/js/theme.js" defer></script>
    <style>
        .question-block {
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f8f9fa;
        }

        body.dark-mode .question-block {
            border-color: #444;
            background-color: #2a2a2a;
        }
    </style>
</head>

<body class="<?= $theme_class ?>">
    <?php include 'admin_nav.php'; ?>
    <div class="container mt-4">
        <h2>Survey Management</h2>
        <?= $message ?>
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card">
                    <div class="card-header">Create a New Survey</div>
                    <div class="card-body">
                        <form action="manage_polls.php" method="POST" id="survey-form">
                            <input type="hidden" name="action" value="add_poll">
                            <div class="mb-3">
                                <label for="title" class="form-label">Survey Title</label>
                                <input type="text" name="title" id="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description (Optional)</label>
                                <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                            </div>
                            <hr>
                            <h5 class="mb-3">Survey Questions</h5>
                            <div id="questions-container">
                            </div>
                            <button type="button" id="add-question-btn" class="btn btn-success mt-2"><i
                                    class="fas fa-plus"></i> Add Question</button>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Publish Survey</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-4">
                <div class="card">
                    <div class="card-header">Survey List</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Respondents</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($polls) > 0):
                                        foreach ($polls as $poll): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($poll['title']) ?></td>
                                                <td><span
                                                        class="badge bg-<?= $poll['status'] === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($poll['status']) ?></span>
                                                </td>
                                                <td><?= $poll['response_count'] ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="poll_results.php?id=<?= $poll['id'] ?>"
                                                            class="btn btn-info btn-sm" title="View Results"><i
                                                                class="fas fa-chart-bar"></i></a>
                                                        <a href="manage_polls.php?action=share&id=<?= $poll['id'] ?>"
                                                            class="btn btn-secondary btn-sm"
                                                            title="Create & Display Public Links"><i
                                                                class="fas fa-share-alt"></i></a>
                                                        <a href="manage_polls.php?action=toggle_status&id=<?= $poll['id'] ?>"
                                                            class="btn btn-warning btn-sm"
                                                            title="<?= $poll['status'] === 'active' ? 'Close Survey' : 'Aktifkan Survei' ?>"><i
                                                                class="fas fa-power-off"></i></a>
                                                        <a href="manage_polls.php?action=delete&id=<?= $poll['id'] ?>"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure you want to delete this survey? All voice data will be deleted.')"
                                                            title="Delete"><i class="fas fa-trash"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No surveys have been conducted yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let questionCounter = 0;
            const questionsContainer = document.getElementById('questions-container');

            const addQuestion = () => {
                questionCounter++;
                const questionId = `q-${questionCounter}`;
                const questionBlock = document.createElement('div');
                questionBlock.className = 'question-block';
                questionBlock.id = questionId;
                questionBlock.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0"><strong>Pertanyaan #${questionCounter}</strong></label>
                    <button type="button" class="btn-close remove-question-btn"></button>
                </div>
                <input type="text" name="questions[${questionId}][text]" class="form-control mb-2" placeholder="Tulis pertanyaan di sini" required>
                <div class="options-for-question">
                    <div class="input-group mb-2">
                        <input type="text" name="questions[${questionId}][options][]" class="form-control" placeholder="Opsi 1" required>
                    </div>
                    <div class="input-group mb-2">
                        <input type="text" name="questions[${questionId}][options][]" class="form-control" placeholder="Opsi 2" required>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary add-option-btn"><i class="fas fa-plus"></i> Tambah Opsi</button>
            `;
                questionsContainer.appendChild(questionBlock);
            };

            document.getElementById('add-question-btn').addEventListener('click', addQuestion);

            questionsContainer.addEventListener('click', function (e) {
                // Hapus Opsi
                if (e.target.closest('.remove-option-btn')) {
                    e.target.closest('.input-group').remove();
                }
                // Tambah Opsi
                if (e.target.closest('.add-option-btn')) {
                    const btn = e.target.closest('.add-option-btn');
                    const optionsContainer = btn.previousElementSibling;
                    const optionCount = optionsContainer.querySelectorAll('input').length + 1;
                    const newOptionDiv = document.createElement('div');
                    newOptionDiv.className = 'input-group mb-2';
                    const questionId = btn.closest('.question-block').id;
                    newOptionDiv.innerHTML = `
                    <input type="text" name="questions[${questionId}][options][]" class="form-control" placeholder="Opsi ${optionCount}">
                    <button class="btn btn-outline-danger remove-option-btn" type="button"><i class="fas fa-times"></i></button>
                `;
                    optionsContainer.appendChild(newOptionDiv);
                }
                // Hapus Pertanyaan
                if (e.target.classList.contains('btn-close') && e.target.classList.contains('remove-question-btn')) {
                    e.target.closest('.question-block').remove();
                }
            });

            // Tambah satu pertanyaan saat halaman dimuat
            addQuestion();
        });
    </script>
</body>

</html>