<?php
require_once __DIR__ . '/config/app.php';

$pageTitle = 'AniTrack - Anime & Manga Tracker';
$activePage = 'home';
require_once __DIR__ . '/includes/header.php';
?>

<style>
body {
  background:
    linear-gradient(rgba(0,0,0,0.72), rgba(0,0,0,0.72)),
    url("<?= BASE_URL ?>/assets/images/big 3 .jpg") center/cover fixed;
}
.landing-panel {
  background: rgba(0,0,0,0.55);
  padding: 50px;
  border-radius: 12px;
  box-shadow: 0 0 30px rgba(0,0,0,0.7);
}
</style>

<section class="hero">
  <div class="hero-content landing-panel">
    <h1>Track Your Anime &amp; Manga</h1>
    <p>
      Rate, review, and organize your favorite anime and manga in one place.
    </p>
    <div class="hero-btns">
      <a href="<?= BASE_URL ?>/auth/register.php" class="btn">Get Started</a>
      <a href="<?= BASE_URL ?>/pages/home.php" class="btn btn-outline">Browse Works</a>
    </div>
  </div>
</section>

<section class="section">
  <h2>Why AniTrack?</h2>
  <p class="section-sub">Everything you need for anime and manga tracking</p>

  <div class="grid">
    <div class="card">
      <div class="card-content" style="text-align:center">
        <h3 style="color:var(--accent)">My Library</h3>
        <p>Save your anime and manga collection in one place.</p>
      </div>
    </div>
    <div class="card">
      <div class="card-content" style="text-align:center">
        <h3 style="color:var(--accent)">Ratings</h3>
        <p>Track ratings, notes, and watching or reading status.</p>
      </div>
    </div>
    <div class="card">
      <div class="card-content" style="text-align:center">
        <h3 style="color:var(--accent)">Cover Images</h3>
        <p>Browse works with cover art from Jikan/MyAnimeList data.</p>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
