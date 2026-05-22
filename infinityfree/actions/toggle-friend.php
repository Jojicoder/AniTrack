<?php
session_start();
require_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid request.');
}

$friendId = (int)($_POST['friend_id'] ?? 0);
$action   = $_POST['action'] ?? '';
$rawRedirect = $_POST['redirect'] ?? '';
$allowed  = ['/pages/dashboard.php', '/pages/user-profile.php'];
$path     = parse_url($rawRedirect, PHP_URL_PATH);
$isRelative = $rawRedirect !== '' && $rawRedirect[0] === '/';
$redirect = ($isRelative && in_array($path, $allowed, true))
    ? $rawRedirect
    : BASE_URL . '/pages/dashboard.php';

if (!$friendId || $friendId === (int)$_SESSION['user_id']) {
    header('Location: ' . $redirect);
    exit;
}

if ($action === 'remove') {
    $stmt = $pdo->prepare('DELETE FROM friends WHERE user_id = ? AND friend_id = ?');
    $stmt->execute([$_SESSION['user_id'], $friendId]);
} else {
    $stmt = $pdo->prepare('
        INSERT IGNORE INTO friends (user_id, friend_id) VALUES (?, ?)
    ');
    $stmt->execute([$_SESSION['user_id'], $friendId]);
}

header('Location: ' . $redirect);
exit;
