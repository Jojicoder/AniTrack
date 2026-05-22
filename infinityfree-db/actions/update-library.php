<?php
session_start();
require_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/library.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$id     = (int)($_POST['id']     ?? 0);
$status = $_POST['status'] ?? '';
$note   = trim($_POST['note']   ?? '');

$ratingRaw = $_POST['rating'] ?? '';
$rating    = ($ratingRaw !== '') ? (int)$ratingRaw : null;

$validStatuses = ['Watching','Completed','Plan to Watch','On Hold','Dropped','Reading','Plan to Read'];

if (!$id || !in_array($status, $validStatuses)) {
    header('Location: ' . BASE_URL . '/pages/library.php');
    exit;
}

if ($rating !== null && ($rating < 1 || $rating > 10)) {
    $rating = null;
}

// Verify this entry belongs to the logged-in user
$stmt = $pdo->prepare('SELECT id FROM user_library WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
if (!$stmt->fetch()) {
    header('Location: ' . BASE_URL . '/pages/library.php');
    exit;
}

$stmt = $pdo->prepare('
    UPDATE user_library SET status = ?, rating = ?, note = ?
    WHERE id = ? AND user_id = ?
');
$stmt->execute([$status, $rating, $note ?: null, $id, $_SESSION['user_id']]);

header('Location: ' . BASE_URL . '/pages/library.php');
exit;
