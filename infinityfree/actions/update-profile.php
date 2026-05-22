<?php
session_start();
require_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/profile.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid request.');
}

$bio = trim($_POST['bio'] ?? '');
if (mb_strlen($bio) > 300) {
    $bio = mb_substr($bio, 0, 300);
}

$stmt = $pdo->prepare('UPDATE users SET bio = ? WHERE id = ?');
$stmt->execute([$bio ?: null, $_SESSION['user_id']]);

header('Location: ' . BASE_URL . '/pages/profile.php?saved=1');
exit;
