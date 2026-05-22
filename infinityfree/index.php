<?php
session_start();
require_once __DIR__ . '/config/app.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

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
  padding: 56px 50px;
  border-radius: 16px;
  box-shadow: 0 0 60px rgba(0,0,0,0.7);
  border: 1px solid rgba(255,255,255,0.06);
}
.hero-badge {
  display: inline-block;
  background: rgba(123,63,242,0.2);
  border: 1px solid rgba(123,63,242,0.45);
  color: var(--accent);
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  padding: 6px 18px;
  border-radius: 100px;
  margin-bottom: 22px;
}
.stats-strip {
  display: flex;
  justify-content: center;
  gap: 48px;
  flex-wrap: wrap;
  padding: 36px 0;
  border-top: 1px solid rgba(255,255,255,0.07);
  border-bottom: 1px solid rgba(255,255,255,0.07);
  margin-bottom: 64px;
}
.stat-item { text-align: center; }
.stat-item .num {
  display: block;
  font-size: 34px;
  font-weight: 800;
  color: var(--accent);
  line-height: 1;
  margin-bottom: 4px;
}
.stat-item .lbl {
  font-size: 12px;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 1.2px;
}
.feature-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 20px;
}
.feature-card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-top: 3px solid var(--accent);
  border-radius: 12px;
  padding: 30px 24px;
  text-align: center;
  transition: transform 200ms, box-shadow 200ms;
}
.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 24px 56px rgba(0,0,0,0.5);
}
.feature-icon { font-size: 44px; display: block; margin-bottom: 14px; }
.feature-title { color: var(--accent); font-size: 17px; font-weight: 800; margin-bottom: 8px; }
.feature-desc { color: var(--muted); font-size: 14px; line-height: 1.6; margin: 0; }
.cta-bottom {
  text-align: center;
  padding: 56px 0 16px;
  border-top: 1px solid rgba(255,255,255,0.07);
  margin-top: 16px;
}
</style>

<section class="hero">
  <div class="hero-content landing-panel">
    <div class="hero-badge"> No Ads &middot; Open to All</div>
    <h1>Track Your Anime &amp; Manga</h1>
    <p>
      Rate, review, and organize all your favorite anime and manga
      in one beautiful place with AniTrack.
    </p>
    <div class="hero-btns">
      <a href="<?= BASE_URL ?>/auth/register.php" class="btn" style="padding:14px 32px;font-size:16px">Get Started — It's Free</a>
      <a href="<?= BASE_URL ?>/pages/home.php" class="btn btn-outline">Browse Works</a>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="stats-strip">
    <div class="stat-item"><span class="num">29</span><span class="lbl">Works</span></div>
    <div class="stat-item"><span class="num">Anime &amp; Manga</span><span class="lbl">Both Supported</span></div>
    <div class="stat-item"><span class="num">10</span><span class="lbl">Point Rating</span></div>
    <div class="stat-item"><span class="num">100%</span><span class="lbl">Free</span></div>
  </div>

  <h2>Why AniTrack?</h2>
  <p class="section-sub">Everything you need for anime and manga tracking</p>

  <div class="feature-grid">
    <div class="feature-card">
      <span class="feature-icon">📚</span>
      <div class="feature-title">My Library</div>
      <p class="feature-desc">Save all your anime and manga collections in one place.</p>
    </div>
    <div class="feature-card">
      <span class="feature-icon">⭐</span>
      <div class="feature-title">Ratings &amp; Reviews</div>
      <p class="feature-desc">Rate your favorite series and share your reviews.</p>
    </div>
    <div class="feature-card">
      <span class="feature-icon">👥</span>
      <div class="feature-title">Friends</div>
      <p class="feature-desc">Connect with friends and see what they are watching.</p>
    </div>
    <div class="feature-card">
      <span class="feature-icon">🖼️</span>
      <div class="feature-title">Anime Covers</div>
      <p class="feature-desc">Add beautiful cover images to make your collection stylish.</p>
    </div>
  </div>

  <div class="cta-bottom">
    <h2 style="font-size:clamp(22px,3vw,36px);margin-bottom:10px">Ready to start tracking?</h2>
    <p class="section-sub" style="margin-bottom:28px">Join AniTrack and build your perfect anime list today.</p>
    <a href="<?= BASE_URL ?>/auth/register.php" class="btn" style="font-size:15px;padding:13px 34px">Create Free Account</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
