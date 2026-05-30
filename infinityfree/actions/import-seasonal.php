<?php
session_start();
require_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/seasonal.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid request.');
}

$malId       = (int)($_POST['mal_id'] ?? 0) ?: null;
$title       = trim($_POST['title'] ?? '');
$titleJp     = trim($_POST['title_jp'] ?? '') ?: null;
$genre       = trim($_POST['genre'] ?? '') ?: null;
$desc        = preg_replace('/\s*\[Written by MAL Rewrite\]\s*/i', ' ', trim($_POST['description'] ?? ''));
$desc        = trim($desc) ?: null;
$imageUrl    = trim($_POST['image_url'] ?? '') ?: null;
$year        = (int)($_POST['release_year'] ?? 0) ?: null;
$eps         = trim($_POST['episodes_chapters'] ?? '') ?: null;
$jikanStatus = trim($_POST['jikan_status'] ?? '');
$libStatus   = trim($_POST['lib_status'] ?? 'Plan to Watch');
$workType    = trim($_POST['work_type'] ?? 'anime');
$redirect    = trim($_POST['redirect'] ?? 'seasonal');

if (!$title) {
    header('Location: ' . BASE_URL . '/pages/seasonal.php');
    exit;
}

if (!in_array($workType, ['anime', 'manga'], true)) {
    $workType = 'anime';
}

if ($workType === 'manga') {
    $airStatus = (stripos($jikanStatus, 'Publishing') !== false || stripos($jikanStatus, 'Ongoing') !== false)
        ? 'Ongoing' : 'Completed';
} else {
    $airStatus = (stripos($jikanStatus, 'Finished') !== false || stripos($jikanStatus, 'Completed') !== false)
        ? 'Completed' : 'Airing';
}

$validStatuses = ['Watching', 'Completed', 'Plan to Watch', 'On Hold', 'Dropped', 'Reading', 'Plan to Read'];
if (!in_array($libStatus, $validStatuses)) {
    $libStatus = 'Plan to Watch';
}

// Find existing work by mal_id or title
$workId = null;

if ($malId) {
    $stmt = $pdo->prepare('SELECT id FROM works WHERE mal_id = ? AND type = ?');
    $stmt->execute([$malId, $workType]);
    $workId = $stmt->fetchColumn() ?: null;
}

if (!$workId) {
    $stmt = $pdo->prepare('SELECT id FROM works WHERE title = ? AND type = ?');
    $stmt->execute([$title, $workType]);
    $workId = $stmt->fetchColumn() ?: null;
    // Backfill mal_id if missing
    if ($workId && $malId) {
        $pdo->prepare('UPDATE works SET mal_id = ? WHERE id = ? AND mal_id IS NULL')
            ->execute([$malId, $workId]);
    }
}

// Insert new work
if (!$workId) {
    $stmt = $pdo->prepare('
        INSERT INTO works (title, title_jp, type, genre, description, image_url, release_year, episodes_chapters, air_status, mal_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$title, $titleJp, $workType, $genre, $desc, $imageUrl, $year, $eps, $airStatus, $malId]);
    $workId = (int)$pdo->lastInsertId();
}

// Add to library (skip if already there)
$pdo->prepare('INSERT IGNORE INTO user_library (user_id, work_id, status) VALUES (?, ?, ?)')
    ->execute([$_SESSION['user_id'], $workId, $libStatus]);

if ($redirect === 'popular') {
    header('Location: ' . BASE_URL . '/pages/popular.php');
} elseif ($redirect === 'daily') {
    header('Location: ' . BASE_URL . '/pages/daily.php');
} else {
    header('Location: ' . BASE_URL . '/pages/seasonal.php');
}
exit;
