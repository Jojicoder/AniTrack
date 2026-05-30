<?php
session_start();
require_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['avatar'])) {
    header('Location: ' . BASE_URL . '/pages/profile.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid request.');
}

$file     = $_FILES['avatar'];
$maxBytes = 500 * 1024; // 500 KB
$allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    header('Location: ' . BASE_URL . '/pages/profile.php?avatar_error=upload');
    exit;
}
if ($file['size'] > $maxBytes) {
    header('Location: ' . BASE_URL . '/pages/profile.php?avatar_error=size');
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);
if (!in_array($mime, $allowed, true)) {
    header('Location: ' . BASE_URL . '/pages/profile.php?avatar_error=type');
    exit;
}

$imageData = file_get_contents($file['tmp_name']);
if ($imageData === false) {
    header('Location: ' . BASE_URL . '/pages/profile.php?avatar_error=save');
    exit;
}
$dataUrl = 'data:' . $mime . ';base64,' . base64_encode($imageData);

$stmt = $pdo->prepare('UPDATE users SET avatar = ? WHERE id = ?');
$stmt->execute([$dataUrl, $_SESSION['user_id']]);
$_SESSION['avatar'] = $dataUrl;

header('Location: ' . BASE_URL . '/pages/profile.php?saved=1');
exit;
