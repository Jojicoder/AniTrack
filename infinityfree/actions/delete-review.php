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

$reviewId  = (int)($_POST['review_id'] ?? 0);
$isAdmin   = ($_SESSION['role'] ?? '') === 'admin';
$rawRedirect = $_POST['redirect'] ?? '';
$path        = parse_url($rawRedirect, PHP_URL_PATH) ?? '';
$isSafe      = $path === '/pages/work-detail.php'
    && (($rawRedirect !== '' && $rawRedirect[0] === '/') || strpos($rawRedirect, BASE_URL) === 0);
$redirect    = $isSafe ? $rawRedirect : BASE_URL . '/pages/dashboard.php';

if ($reviewId) {
    if ($isAdmin) {
        $pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([$reviewId]);
    } else {
        // Users can only delete their own reviews
        $pdo->prepare('DELETE FROM reviews WHERE id = ? AND user_id = ?')
            ->execute([$reviewId, $_SESSION['user_id']]);
    }
}

$adminRedirect = BASE_URL . '/pages/admin-reviews.php';
header('Location: ' . ($isAdmin ? $adminRedirect : $redirect));
exit;
