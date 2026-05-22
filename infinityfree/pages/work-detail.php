<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL . '/pages/home.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM works WHERE id = ?');
$stmt->execute([$id]);
$work = $stmt->fetch();

if (!$work) {
    header('Location: ' . BASE_URL . '/pages/home.php');
    exit;
}

$libraryEntry = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM user_library WHERE user_id = ? AND work_id = ?');
    $stmt->execute([$_SESSION['user_id'], $id]);
    $libraryEntry = $stmt->fetch();
}

$colors = [
    'Action'        => ['#7b3ff2cc', '#7b3ff244'],
    'Adventure'     => ['#1d4ed8cc', '#1d4ed844'],
    'Dark Fantasy'  => ['#6b21a8cc', '#6b21a844'],
    'Historical'    => ['#92400ecc', '#92400e44'],
    'Slice of Life' => ['#0891b2cc', '#0891b244'],
    'Romance'       => ['#db2777cc', '#db277744'],
    'Sci-Fi'        => ['#0369a1cc', '#0369a144'],
    'Sports'        => ['#15803dcc', '#15803d44'],
];
$c = $colors[$work['genre']] ?? ['#7b3ff2cc', '#7b3ff244'];
$jikanCovers = [
    'Demon Slayer'    => 'https://cdn.myanimelist.net/images/anime/1286/99889l.jpg',
    'Attack on Titan' => 'https://cdn.myanimelist.net/images/anime/10/47347l.jpg',
    'Jujutsu Kaisen'  => 'https://cdn.myanimelist.net/images/anime/1171/109222l.jpg',
    'Vinland Saga'    => 'https://cdn.myanimelist.net/images/anime/1500/103005l.jpg',
    'One Piece'       => 'https://cdn.myanimelist.net/images/manga/2/253146l.jpg',
    'Berserk'         => 'https://cdn.myanimelist.net/images/manga/1/157897l.jpg',
    'Blue Period'     => 'https://cdn.myanimelist.net/images/manga/2/204827l.jpg',
    'Chainsaw Man'    => 'https://cdn.myanimelist.net/images/manga/3/216464l.jpg',
];
$coverUrl = $work['image_url'] ?: ($jikanCovers[$work['title']] ?? '');

// Reviews
$stmt = $pdo->prepare('
    SELECT r.*, u.username, u.avatar
    FROM reviews r JOIN users u ON r.user_id = u.id
    WHERE r.work_id = ?
    ORDER BY r.created_at DESC
');
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

$avgRating = null;
if ($reviews) {
    $avgRating = round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1);
}

$myReview = null;
if (isset($_SESSION['user_id'])) {
    foreach ($reviews as $r) {
        if ($r['user_id'] === $_SESSION['user_id']) { $myReview = $r; break; }
    }
}

require_once __DIR__ . '/../includes/helpers.php';
$pageTitle  = htmlspecialchars($work['title']) . ' - AniTrack';
$activePage = 'works';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div style="margin-bottom:16px">
    <a href="<?= BASE_URL ?>/pages/home.php" style="color:var(--muted);font-size:14px">&larr; All Works</a>
  </div>

  <div class="work-detail">
    <div class="work-detail-sidebar">
      <?php if ($coverUrl): ?>
        <img class="work-detail-cover"
             src="<?= htmlspecialchars($coverUrl) ?>"
             alt="<?= htmlspecialchars($work['title']) ?> cover">
      <?php else: ?>
        <div class="work-placeholder work-placeholder-lg"
             style="background:linear-gradient(135deg,<?= $c[0] ?>,<?= $c[1] ?>)">
          <span class="wp-type"><?= strtoupper($work['type']) ?></span>
          <span class="wp-title" style="font-size:22px"><?= htmlspecialchars($work['title']) ?></span>
          <?php if ($work['title_jp']): ?>
            <span class="wp-jp"><?= htmlspecialchars($work['title_jp']) ?></span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="work-detail-info">
      <div>
        <span class="tag tag-<?= $work['type'] ?>"><?= ucfirst($work['type']) ?></span>
        <span class="tag" style="margin-left:6px"><?= htmlspecialchars($work['genre']) ?></span>
      </div>

      <h2 style="margin-top:10px"><?= htmlspecialchars($work['title']) ?></h2>
      <?php if ($work['title_jp']): ?>
        <p class="title-jp"><?= htmlspecialchars($work['title_jp']) ?></p>
      <?php endif; ?>

      <div class="meta-grid">
        <div class="meta-item">
          <div class="meta-label">Year</div>
          <div class="meta-value"><?= $work['release_year'] ?? '—' ?></div>
        </div>
        <div class="meta-item">
          <div class="meta-label"><?= $work['type'] === 'anime' ? 'Episodes' : 'Chapters' ?></div>
          <div class="meta-value"><?= htmlspecialchars($work['episodes_chapters'] ?? '—') ?></div>
        </div>
        <div class="meta-item">
          <div class="meta-label">Status</div>
          <div class="meta-value" style="font-size:15px"><?= htmlspecialchars($work['air_status']) ?></div>
        </div>
      </div>

      <?php if ($work['description']): ?>
        <p class="synopsis"><?= nl2br(htmlspecialchars($work['description'])) ?></p>
      <?php endif; ?>

      <div class="work-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
          <?php if ($libraryEntry): ?>
            <span class="tag tag-status">&#10003; In Your Library — <?= htmlspecialchars($libraryEntry['status']) ?></span>
            <a href="<?= BASE_URL ?>/pages/library.php" class="btn btn-outline">Go to My Library</a>
          <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>/actions/add-to-library.php">
              <?= csrfField() ?>
              <input type="hidden" name="work_id" value="<?= $work['id'] ?>">
              <input type="hidden" name="redirect" value="detail">
              <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
                <div>
                  <label style="color:var(--text);font-weight:800;font-size:14px;display:block;margin-bottom:6px">
                    Status
                  </label>
                  <select name="status" style="min-width:180px">
                    <option value="Plan to Watch">Plan to Watch</option>
                    <option value="Watching">Watching</option>
                    <option value="Completed">Completed</option>
                    <option value="On Hold">On Hold</option>
                    <option value="Dropped">Dropped</option>
                    <option value="Reading">Reading</option>
                    <option value="Plan to Read">Plan to Read</option>
                  </select>
                </div>
                <button type="submit" class="btn">+ Add to My Library</button>
              </div>
            </form>
          <?php endif; ?>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/auth/login.php" class="btn">Login to Add to Library</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0" id="reviews">
  <h3 style="color:var(--accent);font-size:22px;font-weight:800;margin-bottom:6px">
    Reviews
    <?php if ($avgRating !== null): ?>
      <span class="rating-badge" style="margin-left:10px;font-size:16px">&#9733; <?= $avgRating ?> <small>/ 10</small></span>
    <?php endif; ?>
  </h3>
  <p style="color:var(--muted);font-size:14px;margin-bottom:20px"><?= count($reviews) ?> review<?= count($reviews) !== 1 ? 's' : '' ?></p>

  <?php if (isset($_SESSION['user_id'])): ?>
    <div style="background:var(--panel);border:1px solid var(--border);border-radius:10px;padding:22px;margin-bottom:28px">
      <h4 style="color:var(--text);font-weight:800;margin-bottom:16px"><?= $myReview ? 'Edit Your Review' : 'Write a Review' ?></h4>
      <?php if ($_GET['review_error'] ?? ''): ?>
        <div class="alert alert-error" style="margin-bottom:14px">Please fill in all required fields.</div>
      <?php endif; ?>
      <form method="POST" action="<?= BASE_URL ?>/actions/save-review.php">
        <?= csrfField() ?>
        <input type="hidden" name="work_id" value="<?= $id ?>">
        <div style="display:grid;gap:14px">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div>
              <label style="display:block;margin-bottom:6px;font-weight:800;font-size:14px">Rating (1–10) *</label>
              <input type="number" name="rating" min="1" max="10" required
                     value="<?= $myReview ? $myReview['rating'] : '' ?>" placeholder="e.g. 8">
            </div>
            <div>
              <label style="display:block;margin-bottom:6px;font-weight:800;font-size:14px">Your Status</label>
              <select name="user_status">
                <option value="">— select —</option>
                <?php foreach (['Watching','Completed','Plan to Watch','On Hold','Dropped','Reading','Plan to Read'] as $s): ?>
                  <option value="<?= $s ?>" <?= ($myReview['user_status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div>
            <label style="display:block;margin-bottom:6px;font-weight:800;font-size:14px">Review *</label>
            <textarea name="body" rows="4" required placeholder="What did you think?"><?= htmlspecialchars($myReview['body'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn" style="justify-self:start"><?= $myReview ? 'Update Review' : 'Post Review' ?></button>
        </div>
      </form>
    </div>
  <?php else: ?>
    <p style="color:var(--muted);margin-bottom:28px">
      <a href="<?= BASE_URL ?>/auth/login.php" style="color:var(--accent)">Login</a> to write a review.
    </p>
  <?php endif; ?>

  <?php if ($reviews): ?>
    <div class="review-list">
      <?php foreach ($reviews as $r): ?>
      <div class="review-card">
        <div class="review-header">
          <?= avatarImg($r['avatar'], $r['username'], 'mini-avatar') ?>
          <a href="<?= BASE_URL ?>/pages/user-profile.php?id=<?= $r['user_id'] ?>" class="user-link"><?= htmlspecialchars($r['username']) ?></a>
          <span class="rating-badge">&#9733; <?= $r['rating'] ?> <small>/ 10</small></span>
          <span class="review-date"><?= htmlspecialchars(substr($r['created_at'], 0, 10)) ?></span>
        </div>
        <p class="review-text"><?= nl2br(htmlspecialchars($r['body'])) ?></p>
        <?php if ($r['user_status']): ?>
          <p class="review-status">Status: <?= htmlspecialchars($r['user_status']) ?></p>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state"><p>No reviews yet. Be the first to review this work!</p></div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
