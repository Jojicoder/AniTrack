<?php
session_start();
require_once __DIR__ . '/../config/app.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$uid = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT id, username, email, role, avatar, bio, created_at FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch();

// Stats
$stmt = $pdo->prepare('
    SELECT COUNT(*) as total,
           SUM(w.type = "anime") as anime,
           SUM(w.type = "manga") as manga
    FROM user_library ul JOIN works w ON ul.work_id = w.id
    WHERE ul.user_id = ?
');
$stmt->execute([$uid]);
$stats = $stmt->fetch();

$stmt2 = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$stmt2->execute([$uid]);
$reviewCount = (int)$stmt2->fetchColumn();

$stmt3 = $pdo->prepare('SELECT COUNT(*) FROM friends WHERE user_id = ? AND status = "accepted"');
$stmt3->execute([$uid]);
$friendCount = (int)$stmt3->fetchColumn();

$stmt4 = $pdo->prepare('SELECT status, COUNT(*) as cnt FROM user_library WHERE user_id = ? GROUP BY status ORDER BY cnt DESC');
$stmt4->execute([$uid]);
$statusBreakdown = $stmt4->fetchAll(PDO::FETCH_KEY_PAIR);

// Library entries
$stmt = $pdo->prepare('
    SELECT ul.*, w.title, w.type, w.genre, w.image_url
    FROM user_library ul JOIN works w ON ul.work_id = w.id
    WHERE ul.user_id = ?
    ORDER BY ul.created_at DESC
');
$stmt->execute([$uid]);
$entries = $stmt->fetchAll();

// Reviews
$stmt = $pdo->prepare('
    SELECT r.*, w.title as work_title, w.type as work_type
    FROM reviews r JOIN works w ON r.work_id = w.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
');
$stmt->execute([$uid]);
$reviews = $stmt->fetchAll();

$colors  = genreColors();
$saved   = isset($_GET['saved']);
$avatarError = $_GET['avatar_error'] ?? '';
$avatarErrors = [
    'upload' => 'Upload failed. Please try again.',
    'size'   => 'Image must be under 500 KB.',
    'type'   => 'Only JPEG, PNG, GIF and WebP images are allowed.',
    'save'   => 'Could not save the image. Please try again.',
];

$pageTitle  = htmlspecialchars($user['username']) . ' - AniTrack';
$activePage = 'profile';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
.avatar-wrap {
  position: relative;
  width: 72px;
  height: 72px;
  cursor: pointer;
  flex-shrink: 0;
  border-radius: 50%;
  overflow: hidden;
}
.avatar-wrap img,
.avatar-wrap .profile-avatar {
  width: 72px !important;
  height: 72px !important;
  object-fit: cover;
  display: block;
  border-radius: 50%;
}
.avatar-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0,0,0,0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 150ms;
  font-size: 11px;
  font-weight: 800;
  color: #fff;
  text-align: center;
  pointer-events: none;
}
.avatar-wrap:hover .avatar-overlay { opacity: 1; }
</style>

<section class="section">

  <?php if ($saved): ?>
    <div class="alert alert-success" style="margin-bottom:20px">Changes saved successfully.</div>
  <?php endif; ?>
  <?php if ($avatarError && isset($avatarErrors[$avatarError])): ?>
    <div class="alert alert-error" style="margin-bottom:20px"><?= htmlspecialchars($avatarErrors[$avatarError]) ?></div>
  <?php endif; ?>

  <!-- Avatar upload form (hidden, auto-submits) -->
  <form method="POST" action="<?= BASE_URL ?>/actions/upload-avatar.php"
        enctype="multipart/form-data" id="avatar-form" style="display:none">
    <?= csrfField() ?>
    <input type="file" id="avatar-file" name="avatar" accept="image/*">
  </form>

  <div class="profile-header">
    <div class="avatar-wrap" id="avatar-wrap" title="Change photo">
      <?= avatarImg($user['avatar'], $user['username'], 'profile-avatar') ?>
      <div class="avatar-overlay">Change<br>Photo</div>
    </div>
    <div class="profile-info">
      <h3><?= htmlspecialchars($user['username']) ?>
        <?php if ($user['role'] === 'admin'): ?>
          <span class="badge badge-admin" style="margin-left:8px">Admin</span>
        <?php endif; ?>
      </h3>
      <p>Joined: <?= htmlspecialchars(substr($user['created_at'], 0, 10)) ?></p>
      <p style="margin-top:4px">
        <a href="<?= BASE_URL ?>/pages/user-profile.php?id=<?= $uid ?>" style="color:var(--accent);font-size:13px">View public profile</a>
      </p>
    </div>
  </div>

  <!-- Bio -->
  <form method="POST" action="<?= BASE_URL ?>/actions/update-profile.php">
    <?= csrfField() ?>
    <div style="background:var(--panel);border:1px solid var(--border);border-radius:10px;padding:20px;margin-bottom:28px">
      <label style="display:block;font-size:13px;font-weight:800;color:var(--muted);letter-spacing:1px;text-transform:uppercase;margin-bottom:8px">Bio</label>
      <textarea name="bio" maxlength="300" placeholder="Write something about yourself..."
                style="min-height:80px;resize:vertical;font-size:14px"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
      <div style="display:flex;justify-content:flex-end;margin-top:8px">
        <button type="submit" class="btn btn-sm">Save Bio</button>
      </div>
    </div>
  </form>

  <div class="stats-grid" style="margin-bottom:16px">
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['total'] ?></div><div class="stat-label">Library Entries</div></div>
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['anime'] ?></div><div class="stat-label">Anime</div></div>
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['manga'] ?></div><div class="stat-label">Manga</div></div>
    <div class="stat-card"><div class="stat-num"><?= $reviewCount ?></div><div class="stat-label">Reviews</div></div>
    <div class="stat-card"><div class="stat-num"><?= $friendCount ?></div><div class="stat-label">Friends</div></div>
  </div>

  <?php if ($statusBreakdown): ?>
  <?php
    $statusColors = [
      'Completed'    => ['#22c55e', '#166534'],
      'Watching'     => ['#3b82f6', '#1e3a5f'],
      'Reading'      => ['#8b5cf6', '#3b1f6e'],
      'Plan to Watch'=> ['#f59e0b', '#78350f'],
      'Plan to Read' => ['#f97316', '#7c2d12'],
      'On Hold'      => ['#94a3b8', '#334155'],
      'Dropped'      => ['#ef4444', '#7f1d1d'],
    ];
  ?>
  <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:32px">
    <?php foreach ($statusBreakdown as $status => $cnt):
      $col = $statusColors[$status] ?? ['#7b3ff2', '#2e1065'];
    ?>
    <div style="display:flex;align-items:center;gap:7px;background:<?= $col[1] ?>22;border:1px solid <?= $col[0] ?>55;border-radius:999px;padding:5px 14px">
      <span style="width:8px;height:8px;border-radius:50%;background:<?= $col[0] ?>;display:inline-block;flex-shrink:0"></span>
      <span style="font-size:13px;font-weight:700;color:<?= $col[0] ?>"><?= htmlspecialchars($status) ?></span>
      <span style="font-size:13px;font-weight:800;color:var(--text)"><?= (int)$cnt ?></span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <h3 style="color:var(--accent);font-size:20px;font-weight:800;margin-bottom:14px">My Library</h3>
  <?php if ($entries): ?>
    <div class="entry-list" style="margin-bottom:40px">
      <?php foreach ($entries as $e):
        $c = $colors[$e['genre']] ?? ['#7b3ff2cc', '#7b3ff244'];
      ?>
      <div class="entry-card">
        <?php if ($e['image_url']): ?>
          <img class="entry-thumb" src="<?= htmlspecialchars($e['image_url']) ?>" alt="">
        <?php else: ?>
          <div class="entry-thumb-placeholder" style="background:linear-gradient(135deg,<?= $c[0] ?>,<?= $c[1] ?>)"><?= strtoupper($e['type']) ?></div>
        <?php endif; ?>
        <div class="entry-info">
          <h3><?= htmlspecialchars($e['title']) ?></h3>
          <p>
            <span class="tag tag-<?= $e['type'] ?>" style="font-size:11px"><?= ucfirst($e['type']) ?></span>
            &nbsp;<span class="tag" style="font-size:11px"><?= htmlspecialchars($e['genre']) ?></span>
          </p>
          <p>Status: <strong><?= htmlspecialchars($e['status']) ?></strong></p>
          <p><?= starsHtml($e['rating']) ?></p>
          <?php if ($e['note']): ?>
            <p style="font-size:13px;font-style:italic;color:var(--muted)">&ldquo;<?= htmlspecialchars($e['note']) ?>&rdquo;</p>
          <?php endif; ?>
          <div class="entry-actions">
            <a href="<?= BASE_URL ?>/pages/work-detail.php?id=<?= $e['work_id'] ?>" class="btn btn-sm btn-outline">View Work</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state" style="margin-bottom:40px">
      <p>No library entries yet. <a href="<?= BASE_URL ?>/pages/home.php" class="btn btn-sm" style="display:inline-block;margin-top:8px">Browse Works</a></p>
    </div>
  <?php endif; ?>

  <h3 style="color:var(--accent);font-size:20px;font-weight:800;margin-bottom:14px">My Reviews</h3>
  <?php if ($reviews): ?>
    <div class="review-list">
      <?php foreach ($reviews as $r): ?>
      <div class="review-card">
        <div class="review-header">
          <a href="<?= BASE_URL ?>/pages/work-detail.php?id=<?= $r['work_id'] ?>" style="font-weight:800;color:var(--text)"><?= htmlspecialchars($r['work_title']) ?></a>
          <span class="tag tag-<?= $r['work_type'] ?>" style="font-size:11px"><?= ucfirst($r['work_type']) ?></span>
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
    <div class="empty-state"><p>No reviews yet.</p></div>
  <?php endif; ?>

</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
document.getElementById('avatar-wrap').addEventListener('click', function() {
  document.getElementById('avatar-file').click();
});
document.getElementById('avatar-file').addEventListener('change', function() {
  if (this.files.length) document.getElementById('avatar-form').submit();
});
</script>
