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

$uid      = (int)$_SESSION['user_id'];
$senderId = (int)($_POST['sender_id'] ?? 0);
$action   = $_POST['action'] ?? '';

if (!$senderId || $senderId === $uid || !in_array($action, ['accept', 'decline'])) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

// Verify a pending request from sender to current user actually exists
$stmt = $pdo->prepare('SELECT id FROM friends WHERE user_id = ? AND friend_id = ? AND status = "pending"');
$stmt->execute([$senderId, $uid]);
if (!$stmt->fetch()) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

if ($action === 'accept') {
    // Mark original request as accepted
    $pdo->prepare('UPDATE friends SET status = "accepted" WHERE user_id = ? AND friend_id = ?')
        ->execute([$senderId, $uid]);
    // Create the reverse row so both sides show the friendship
    $pdo->prepare('INSERT IGNORE INTO friends (user_id, friend_id, status) VALUES (?, ?, "accepted")')
        ->execute([$uid, $senderId]);
} else {
    // Decline: just delete the request
    $pdo->prepare('DELETE FROM friends WHERE user_id = ? AND friend_id = ?')
        ->execute([$senderId, $uid]);
}

header('Location: ' . BASE_URL . '/pages/dashboard.php');
exit;
