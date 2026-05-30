<?php
session_start();
require_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/home.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid request.');
}

$workId  = (int)($_POST['work_id'] ?? 0);
$rating  = (int)($_POST['rating']  ?? 0);
$body    = trim($_POST['body']     ?? '');
$status  = trim($_POST['user_status'] ?? '');
$validStatuses = ['Watching','Completed','Plan to Watch','On Hold','Dropped','Reading','Plan to Read'];

if (!$workId || $rating < 1 || $rating > 10 || $body === '') {
    header('Location: ' . BASE_URL . '/pages/work-detail.php?id=' . $workId . '&review_error=1');
    exit;
}

if ($status !== '' && !in_array($status, $validStatuses, true)) {
    $status = '';
}

$stmt = $pdo->prepare('SELECT id FROM works WHERE id = ?');
$stmt->execute([$workId]);
if (!$stmt->fetch()) {
    header('Location: ' . BASE_URL . '/pages/home.php');
    exit;
}

$stmt = $pdo->prepare('
    INSERT INTO reviews (work_id, user_id, rating, body, user_status)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE rating = VALUES(rating), body = VALUES(body), user_status = VALUES(user_status)
');
$stmt->execute([$workId, $_SESSION['user_id'], $rating, $body, $status ?: null]);

if ($status !== '') {
    $stmt = $pdo->prepare('
        INSERT INTO user_library (user_id, work_id, status, rating)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE status = VALUES(status), rating = VALUES(rating)
    ');
    $stmt->execute([$_SESSION['user_id'], $workId, $status, $rating]);
}

header('Location: ' . BASE_URL . '/pages/work-detail.php?id=' . $workId . '#reviews');
exit;
