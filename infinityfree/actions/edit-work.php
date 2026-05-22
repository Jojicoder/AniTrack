<?php
session_start();
require_once __DIR__ . '/../config/app.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/admin-works.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid request.');
}

$workId      = (int)($_POST['work_id'] ?? 0);
$title       = trim($_POST['title'] ?? '');
$titleJp     = trim($_POST['title_jp'] ?? '');
$type        = $_POST['type'] ?? '';
$genre       = trim($_POST['genre'] ?? '');
$description = trim($_POST['description'] ?? '');
$releaseYear = (int)($_POST['release_year'] ?? 0);
$eps         = trim($_POST['episodes_chapters'] ?? '');
$airStatus   = $_POST['air_status'] ?? '';
$imageUrl    = trim($_POST['image_url'] ?? '');

$validTypes    = ['anime', 'manga'];
$validStatuses = ['Airing', 'Ongoing', 'Completed'];

if (!$workId || $title === '' || !in_array($type, $validTypes, true) ||
    $genre === '' || $description === '' || $releaseYear < 1900 ||
    !in_array($airStatus, $validStatuses, true)) {
    header('Location: ' . BASE_URL . '/pages/admin-works.php?error=missing');
    exit;
}

if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
    header('Location: ' . BASE_URL . '/pages/admin-works.php?error=image');
    exit;
}

$stmt = $pdo->prepare('
    UPDATE works SET
        title = ?, title_jp = ?, type = ?, genre = ?, description = ?,
        release_year = ?, episodes_chapters = ?, air_status = ?, image_url = ?
    WHERE id = ?
');
$stmt->execute([
    $title,
    $titleJp ?: null,
    $type,
    $genre,
    $description,
    $releaseYear,
    $eps ?: null,
    $airStatus,
    $imageUrl ?: null,
    $workId,
]);

header('Location: ' . BASE_URL . '/pages/admin-works.php?saved=1');
exit;
