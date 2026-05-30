<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!defined('BASE_URL')) require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/helpers.php';
$activePage = $activePage ?? '';
$loggedIn   = isset($_SESSION['user_id']);

// Re-read role from DB to reflect changes (e.g. admin demotion) without waiting for re-login
if ($loggedIn && isset($pdo)) {
    $roleStmt = $pdo->prepare('SELECT role FROM users WHERE id = ?');
    $roleStmt->execute([$_SESSION['user_id']]);
    $currentRole = $roleStmt->fetchColumn();
    if ($currentRole === false) {
        // Account deleted — force logout
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header('Location: ' . BASE_URL . '/');
        exit;
    }
    $_SESSION['role'] = $currentRole;
}

$isAdmin = $loggedIn && ($_SESSION['role'] ?? '') === 'admin';

$pendingRequestCount = 0;
if ($loggedIn && isset($pdo)) {
    $nStmt = $pdo->prepare('SELECT COUNT(*) FROM friends WHERE friend_id = ? AND status = "pending"');
    $nStmt->execute([$_SESSION['user_id']]);
    $pendingRequestCount = (int)$nStmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'AniTrack') ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=20260529-daily">
</head>
<body>

<header>
  <nav class="navbar">
    <a href="<?= BASE_URL . ($loggedIn ? '/pages/dashboard.php' : '/') ?>" class="logo">
      <img src="<?= BASE_URL ?>/assets/images/anime logo.png" alt="" aria-hidden="true">
      <span>AniTrack</span>
    </a>
    <ul class="nav-links">
      <?php if (!$loggedIn): ?>
        <li><a href="<?= BASE_URL ?>/"<?= $activePage === 'home' ? ' class="active"' : '' ?>>Home</a></li>
      <?php endif; ?>
      <li><a href="<?= BASE_URL ?>/pages/home.php"<?= $activePage === 'works' ? ' class="active"' : '' ?>>Works</a></li>
      <li><a href="<?= BASE_URL ?>/pages/seasonal.php"<?= $activePage === 'seasonal' ? ' class="active"' : '' ?>>Seasonal</a></li>
      <li><a href="<?= BASE_URL ?>/pages/popular.php"<?= $activePage === 'popular' ? ' class="active"' : '' ?>>Popular</a></li>
      <li><a href="<?= BASE_URL ?>/pages/daily.php"<?= $activePage === 'daily' ? ' class="active"' : '' ?>>Daily Pick</a></li>
      <?php if ($loggedIn): ?>
        <li>
          <a href="<?= BASE_URL ?>/pages/dashboard.php"<?= $activePage === 'dashboard' ? ' class="active"' : '' ?> style="display:flex;align-items:center;gap:6px">
            Dashboard
            <?php if ($pendingRequestCount > 0): ?>
              <span style="background:var(--accent);color:#fff;border-radius:999px;font-size:11px;font-weight:800;padding:1px 7px;line-height:1.6"><?= $pendingRequestCount ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li><a href="<?= BASE_URL ?>/pages/admin-add-work.php"<?= $activePage === 'add-work' ? ' class="active"' : '' ?>>Add Work</a></li>
        <?php if ($isAdmin): ?>
          <li class="has-dropdown">
            <button type="button" class="dropdown-trigger<?= $activePage === 'admin' ? ' active' : '' ?>" aria-haspopup="true" aria-expanded="false">Admin &#9662;</button>
            <ul class="dropdown-menu">
              <li><a href="<?= BASE_URL ?>/pages/admin-dashboard.php">Dashboard</a></li>
              <li><a href="<?= BASE_URL ?>/pages/admin-works.php">Manage Works</a></li>
              <li><a href="<?= BASE_URL ?>/pages/admin-reviews.php">Manage Reviews</a></li>
              <li><a href="<?= BASE_URL ?>/pages/admin-users.php">Manage Users</a></li>
            </ul>
          </li>
        <?php endif; ?>
        <li>
          <a href="<?= BASE_URL ?>/pages/profile.php"<?= $activePage === 'profile' ? ' class="active"' : '' ?> style="display:flex;align-items:center;gap:8px">
            <?= avatarImg($_SESSION['avatar'] ?? null, $_SESSION['username'], 'mini-avatar') ?>
            <?= htmlspecialchars($_SESSION['username']) ?>
          </a>
        </li>
        <li><a href="<?= BASE_URL ?>/auth/logout.php">Logout</a></li>
      <?php else: ?>
        <li><a href="<?= BASE_URL ?>/auth/login.php"<?= $activePage === 'login' ? ' class="active"' : '' ?>>Login</a></li>
        <li><a href="<?= BASE_URL ?>/auth/register.php"<?= $activePage === 'register' ? ' class="active"' : '' ?>>Register</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>
