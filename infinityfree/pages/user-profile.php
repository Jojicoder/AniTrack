<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$profileId = (int)($_GET['id'] ?? 0);
if (!$profileId) {
    header('Location: ' . BASE_URL . '/pages/home.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, username, role, avatar, bio, created_at FROM users WHERE id = ?');
$stmt->execute([$profileId]);
$profileUser = $stmt->fetch();
if (!$profileUser) {
    header('Location: ' . BASE_URL . '/pages/home.php');
    exit;
}

$uid      = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$isSelf   = $uid && $uid === $profileId;
$isFriend = false;
$iSentRequest    = false;
$theySentRequest = false;

if ($uid && !$isSelf) {
    $stmt2 = $pdo->prepare('SELECT status FROM friends WHERE user_id = ? AND friend_id = ?');
    $stmt2->execute([$uid, $profileId]);
    $row = $stmt2->fetch();
    if ($row && $row['status'] === 'accepted') $isFriend = true;
    if ($row && $row['status'] === 'pending')  $iSentRequest = true;

    if (!$isFriend) {
        $stmt2 = $pdo->prepare('SELECT id FROM friends WHERE user_id = ? AND friend_id = ? AND status = "pending"');
        $stmt2->execute([$profileId, $uid]);
        if ($stmt2->fetch()) $theySentRequest = true;
    }
}

// Stats
$stmt = $pdo->prepare('
    SELECT COUNT(*) as total,
           SUM(w.type = "anime") as anime,
           SUM(w.type = "manga") as manga
    FROM user_library ul JOIN works w ON ul.work_id = w.id
    WHERE ul.user_id = ?
');
$stmt->execute([$profileId]);
$stats = $stmt->fetch();

$stmt2 = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$stmt2->execute([$profileId]);
$reviewCount = (int)$stmt2->fetchColumn();

$stmt3 = $pdo->prepare('SELECT COUNT(*) FROM friends WHERE user_id = ? AND status = "accepted"');
$stmt3->execute([$profileId]);
$friendCount = (int)$stmt3->fetchColumn();

// Compatibility score
$compatibility = null;
$commonWorks   = 0;
if ($uid && !$isSelf) {
    $stmt = $pdo->prepare('
        SELECT COUNT(*) FROM user_library a
        JOIN user_library b ON a.work_id = b.work_id
        WHERE a.user_id = ? AND b.user_id = ?
    ');
    $stmt->execute([$uid, $profileId]);
    $commonWorks = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM user_library WHERE user_id = ?');
    $stmt->execute([$uid]);
    $myTotal    = (int)$stmt->fetchColumn();
    $theirTotal = (int)$stats['total'];
    $union      = $myTotal + $theirTotal - $commonWorks;
    $compatibility = $union > 0 ? round(($commonWorks / $union) * 100) : 0;
}

// Library
$stmt = $pdo->prepare('
    SELECT ul.*, w.title, w.type, w.genre, w.image_url
    FROM user_library ul JOIN works w ON ul.work_id = w.id
    WHERE ul.user_id = ?
    ORDER BY ul.created_at DESC
');
$stmt->execute([$profileId]);
$entries = $stmt->fetchAll();

// Reviews
$stmt = $pdo->prepare('
    SELECT r.*, w.title as work_title, w.type as work_type
    FROM reviews r JOIN works w ON r.work_id = w.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
');
$stmt->execute([$profileId]);
$reviews = $stmt->fetchAll();

$colors     = genreColors();
$pageTitle  = htmlspecialchars($profileUser['username']) . ' - AniTrack';
$activePage = '';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div style="margin-bottom:16px">
    <a href="<?= BASE_URL ?>/pages/dashboard.php" style="color:var(--muted);font-size:14px">&larr; Back to Dashboard</a>
  </div>

  <div class="profile-header">
    <?= avatarImg($profileUser['avatar'], $profileUser['username'], 'profile-avatar') ?>
    <div class="profile-info">
      <h3><?= htmlspecialchars($profileUser['username']) ?>
        <?php if ($profileUser['role'] === 'admin'): ?>
          <span class="badge badge-admin" style="margin-left:8px">Admin</span>
        <?php endif; ?>
      </h3>
      <p>Joined: <?= htmlspecialchars(substr($profileUser['created_at'], 0, 10)) ?></p>
      <?php if ($profileUser['bio']): ?>
        <p style="margin-top:8px;font-size:14px;color:var(--text)"><?= htmlspecialchars($profileUser['bio']) ?></p>
      <?php endif; ?>
    </div>
    <?php if ($uid && !$isSelf): ?>
      <div style="margin-left:auto;display:flex;flex-direction:column;gap:8px;align-items:flex-end">
        <?php if ($isFriend): ?>
          <form method="POST" action="<?= BASE_URL ?>/actions/toggle-friend.php">
            <?= csrfField() ?>
            <input type="hidden" name="friend_id" value="<?= $profileId ?>">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars(BASE_URL . '/pages/user-profile.php?id=' . $profileId) ?>">
            <button type="submit" class="btn btn-muted">Friends ✓</button>
          </form>
        <?php elseif ($theySentRequest): ?>
          <div style="display:flex;gap:8px">
            <form method="POST" action="<?= BASE_URL ?>/actions/respond-friend.php">
              <?= csrfField() ?>
              <input type="hidden" name="sender_id" value="<?= $profileId ?>">
              <input type="hidden" name="action" value="accept">
              <button type="submit" class="btn">Accept Request</button>
            </form>
            <form method="POST" action="<?= BASE_URL ?>/actions/respond-friend.php">
              <?= csrfField() ?>
              <input type="hidden" name="sender_id" value="<?= $profileId ?>">
              <input type="hidden" name="action" value="decline">
              <button type="submit" class="btn btn-muted">Decline</button>
            </form>
          </div>
        <?php elseif ($iSentRequest): ?>
          <span class="btn btn-muted" style="opacity:.7;cursor:default">Request Sent</span>
        <?php else: ?>
          <form method="POST" action="<?= BASE_URL ?>/actions/toggle-friend.php">
            <?= csrfField() ?>
            <input type="hidden" name="friend_id" value="<?= $profileId ?>">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars(BASE_URL . '/pages/user-profile.php?id=' . $profileId) ?>">
            <button type="submit" class="btn">+ Add Friend</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="stats-grid" style="margin-bottom:<?= $compatibility !== null ? '16px' : '32px' ?>">
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['total'] ?></div><div class="stat-label">Library Entries</div></div>
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['anime'] ?></div><div class="stat-label">Anime</div></div>
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['manga'] ?></div><div class="stat-label">Manga</div></div>
    <div class="stat-card"><div class="stat-num"><?= $reviewCount ?></div><div class="stat-label">Reviews</div></div>
    <div class="stat-card"><div class="stat-num"><?= $friendCount ?></div><div class="stat-label">Friends</div></div>
  </div>

  <?php if ($compatibility !== null): ?>
  <div style="background:var(--panel);border:1px solid var(--accent);border-radius:10px;padding:16px 20px;margin-bottom:32px;display:flex;align-items:center;gap:16px">
    <div style="font-size:38px;font-weight:800;color:var(--accent);line-height:1"><?= $compatibility ?>%</div>
    <div>
      <div style="font-weight:800;font-size:15px;color:var(--text)">Taste Match</div>
      <div style="font-size:13px;color:var(--muted)"><?= $commonWorks ?> work<?= $commonWorks !== 1 ? 's' : '' ?> in common</div>
    </div>
  </div>
  <?php endif; ?>

  <h3 style="color:var(--accent);font-size:20px;font-weight:800;margin-bottom:14px">Library</h3>
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
    <div class="empty-state" style="margin-bottom:40px"><p>No library entries yet.</p></div>
  <?php endif; ?>

  <h3 style="color:var(--accent);font-size:20px;font-weight:800;margin-bottom:14px">Review History</h3>
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
