<?php
session_start();
require_once __DIR__ . '/config/app.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/home.php');
    exit;
}

$pageTitle  = 'AniTrack - Anime & Manga Tracker';
$activePage = '';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero-content">
    <h1>Track Your Anime &amp; Manga</h1>
    <p>Rate, review, and organize everything you've watched and read — all in one place.</p>
    <div class="hero-btns">
      <a href="<?= BASE_URL ?>/auth/register.php" class="btn">Get Started</a>
      <a href="<?= BASE_URL ?>/pages/home.php" class="btn btn-outline">Browse Works</a>
    </div>
  </div>
</section>

<section class="section">
  <h2>Why AniTrack?</h2>
  <p class="section-sub">Everything you need to manage your anime &amp; manga list</p>
  <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-top: 36px;">
    <div class="card">
      <div class="card-content" style="padding:28px 24px">
        <div style="font-size:36px;margin-bottom:14px">&#128218;</div>
        <h3>Personal Library</h3>
        <p>Keep track of everything you're watching and reading with your own organized list.</p>
      </div>
    </div>
    <div class="card">
      <div class="card-content" style="padding:28px 24px">
        <div style="font-size:36px;margin-bottom:14px">&#11088;</div>
        <h3>Rate &amp; Review</h3>
        <p>Score your favorites and write notes to remember what made them great.</p>
      </div>
    </div>
    <div class="card">
      <div class="card-content" style="padding:28px 24px">
        <div style="font-size:36px;margin-bottom:14px">&#128269;</div>
        <h3>Discover New Titles</h3>
        <p>Browse our curated collection of anime and manga across all genres.</p>
      </div>
    </div>
    <div class="card">
      <div class="card-content" style="padding:28px 24px">
        <div style="font-size:36px;margin-bottom:14px">&#127760;</div>
        <h3>Access Anywhere</h3>
        <p>Your library is stored online — access it from any device, anytime.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top: 0">
  <h2>Get Started Today</h2>
  <p class="section-sub">Create your free account and start tracking</p>
  <div style="text-align:center;margin-top:28px;display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
    <a href="<?= BASE_URL ?>/auth/register.php" class="btn">Create Account</a>
    <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-outline">Log In</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
