<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!defined('BASE_URL')) require_once __DIR__ . '/../config/app.php';
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'AniTrack') ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<header>
  <nav class="navbar">
    <a href="<?= BASE_URL ?>/" class="logo">AniTrack</a>
    <ul class="nav-links">
      <li><a href="<?= BASE_URL ?>/pages/home.php"<?= $activePage === 'home' ? ' class="active"' : '' ?>>Browse Works</a></li>
      <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="<?= BASE_URL ?>/pages/library.php"<?= $activePage === 'library' ? ' class="active"' : '' ?>>My Library</a></li>
        <li class="has-dropdown">
          <a href="#"><?= htmlspecialchars($_SESSION['username']) ?> &#9662;</a>
          <ul class="dropdown-menu">
            <li><a href="<?= BASE_URL ?>/auth/logout.php">Logout</a></li>
          </ul>
        </li>
      <?php else: ?>
        <li><a href="<?= BASE_URL ?>/auth/login.php"<?= $activePage === 'login' ? ' class="active"' : '' ?>>Login</a></li>
        <li><a href="<?= BASE_URL ?>/auth/register.php"<?= $activePage === 'register' ? ' class="active"' : '' ?>>Register</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>
