<?php
session_start();
require_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/pages/library.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$id = (int)($_POST['id'] ?? 0);

if ($id) {
    // AND user_id check prevents deleting other users' entries
    $stmt = $pdo->prepare('DELETE FROM user_library WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
}

header('Location: ' . BASE_URL . '/pages/library.php');
exit;
