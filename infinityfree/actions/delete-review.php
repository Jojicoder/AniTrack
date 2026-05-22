<?php
session_start();
require_once __DIR__ . '/../config/app.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/admin-reviews.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid request.');
}

$reviewId = (int)($_POST['review_id'] ?? 0);
if ($reviewId) {
    $pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([$reviewId]);
}

header('Location: ' . BASE_URL . '/pages/admin-reviews.php');
exit;
