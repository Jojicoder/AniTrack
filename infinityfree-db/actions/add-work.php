<?php
session_start();
require_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/admin-add-work.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$title            = trim($_POST['title'] ?? '');
$titleJp          = trim($_POST['title_jp'] ?? '');
$type             = $_POST['type'] ?? '';
$genre            = trim($_POST['genre'] ?? '');
$description      = trim($_POST['description'] ?? '');
$releaseYear      = (int)($_POST['release_year'] ?? 0);
$episodesChapters = trim($_POST['episodes_chapters'] ?? '');
$airStatus        = $_POST['air_status'] ?? '';
$imageUrl         = trim($_POST['image_url'] ?? '');

$validTypes = ['anime', 'manga'];
$validStatuses = ['Airing', 'Ongoing', 'Completed'];

if (
    $title === '' ||
    !in_array($type, $validTypes, true) ||
    $genre === '' ||
    $description === '' ||
    $releaseYear < 1900 ||
    !in_array($airStatus, $validStatuses, true)
) {
    header('Location: ' . BASE_URL . '/pages/admin-add-work.php?error=missing');
    exit;
}

if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
    header('Location: ' . BASE_URL . '/pages/admin-add-work.php?error=image');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM works WHERE LOWER(title) = LOWER(?) LIMIT 1');
$stmt->execute([$title]);
if ($stmt->fetch()) {
    header('Location: ' . BASE_URL . '/pages/admin-add-work.php?error=duplicate');
    exit;
}

$stmt = $pdo->prepare('
    INSERT INTO works
      (title, title_jp, type, genre, description, image_url, release_year, episodes_chapters, air_status)
    VALUES
      (?, ?, ?, ?, ?, ?, ?, ?, ?)
');
$stmt->execute([
    $title,
    $titleJp !== '' ? $titleJp : null,
    $type,
    $genre,
    $description,
    $imageUrl !== '' ? $imageUrl : null,
    $releaseYear,
    $episodesChapters !== '' ? $episodesChapters : null,
    $airStatus,
]);

header('Location: ' . BASE_URL . '/pages/home.php');
exit;
