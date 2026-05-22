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

$file   = $_FILES['avatar'];
$maxBytes = 5 * 1024 * 1024; // 5 MB
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

$mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
$ext = $mimeToExt[$mime];
$filename = 'user_' . (int)$_SESSION['user_id'] . '_' . time() . '.' . $ext;
$uploadDir = __DIR__ . '/../uploads/avatars/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Delete old avatar file if exists
$stmt = $pdo->prepare('SELECT avatar FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$old = $stmt->fetchColumn();
if ($old && file_exists($uploadDir . $old)) {
    unlink($uploadDir . $old);
}

if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
    header('Location: ' . BASE_URL . '/pages/profile.php?avatar_error=save');
    exit;
}

$stmt = $pdo->prepare('UPDATE users SET avatar = ? WHERE id = ?');
$stmt->execute([$filename, $_SESSION['user_id']]);
$_SESSION['avatar'] = $filename;

header('Location: ' . BASE_URL . '/pages/profile.php?saved=1');
exit;
