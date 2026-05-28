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

$workId   = (int)($_POST['work_id'] ?? 0);
$status   = $_POST['status']   ?? 'Plan to Watch';
$redirect = $_POST['redirect'] ?? 'home';

$validStatuses = ['Watching','Completed','Plan to Watch','On Hold','Dropped','Reading','Plan to Read'];

if (!$workId || !in_array($status, $validStatuses)) {
    header('Location: ' . BASE_URL . '/pages/home.php');
    exit;
}

// Verify the work exists
$stmt = $pdo->prepare('SELECT id FROM works WHERE id = ?');
$stmt->execute([$workId]);
if (!$stmt->fetch()) {
    header('Location: ' . BASE_URL . '/pages/home.php');
    exit;
}

$stmt = $pdo->prepare('
    INSERT INTO user_library (user_id, work_id, status)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE status = VALUES(status)
');
$stmt->execute([$_SESSION['user_id'], $workId, $status]);

if ($redirect === 'detail') {
    header('Location: ' . BASE_URL . '/pages/work-detail.php?id=' . $workId);
} elseif ($redirect === 'seasonal') {
    header('Location: ' . BASE_URL . '/pages/seasonal.php');
} else {
    header('Location: ' . BASE_URL . '/pages/home.php');
}
exit;
