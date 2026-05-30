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

// Stats
$stmt = $pdo->prepare('
    SELECT COUNT(*) as total,
           SUM(w.type = "anime") as anime,
           SUM(w.type = "manga") as manga,
           SUM(ul.status = "Completed") as completed
    FROM user_library ul JOIN works w ON ul.work_id = w.id
    WHERE ul.user_id = ?
');
$stmt->execute([$uid]);
$stats = $stmt->fetch();

$stmt2 = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$stmt2->execute([$uid]);
$reviewCount = (int)$stmt2->fetchColumn();

// Accepted friends
$stmt3 = $pdo->prepare('SELECT COUNT(*) FROM friends WHERE user_id = ? AND status = "accepted"');
$stmt3->execute([$uid]);
$friendCount = (int)$stmt3->fetchColumn();

// Recent library entries (last 6, ordered by latest update or add)
$stmt = $pdo->prepare('
    SELECT ul.*, w.title, w.type, w.genre, w.image_url
    FROM user_library ul JOIN works w ON ul.work_id = w.id
    WHERE ul.user_id = ?
    ORDER BY COALESCE(ul.updated_at, ul.created_at) DESC
    LIMIT 6
');
$stmt->execute([$uid]);
$recentEntries = $stmt->fetchAll();

// Accepted friends list
$stmt = $pdo->prepare('
    SELECT u.id, u.username, u.avatar
    FROM friends f JOIN users u ON f.friend_id = u.id
    WHERE f.user_id = ? AND f.status = "accepted"
    ORDER BY f.created_at DESC
');
$stmt->execute([$uid]);
$friends = $stmt->fetchAll();

// Pending incoming requests
$stmt = $pdo->prepare('
    SELECT u.id, u.username, u.avatar, f.created_at as requested_at
    FROM friends f JOIN users u ON f.user_id = u.id
    WHERE f.friend_id = ? AND f.status = "pending"
    ORDER BY f.created_at DESC
');
$stmt->execute([$uid]);
$pendingRequests = $stmt->fetchAll();

// IDs of users with outgoing pending requests from current user
$stmt = $pdo->prepare('SELECT friend_id FROM friends WHERE user_id = ? AND status = "pending"');
$stmt->execute([$uid]);
$sentRequestIds = array_map('intval', array_column($stmt->fetchAll(), 'friend_id'));

// Friends activity feed
$activityFeed = [];
if ($friends) {
    $friendIds    = array_map('intval', array_column($friends, 'id'));
    $placeholders = implode(',', array_fill(0, count($friendIds), '?'));
    $stmt = $pdo->prepare('
        SELECT ul.created_at, ul.status, ul.rating,
               u.id as uid, u.username, u.avatar,
               w.id as wid, w.title, w.type
        FROM user_library ul
        JOIN users u ON ul.user_id = u.id
        JOIN works w ON ul.work_id = w.id
        WHERE ul.user_id IN (' . $placeholders . ')
        ORDER BY ul.created_at DESC
        LIMIT 10
    ');
    $stmt->execute($friendIds);
    $activityFeed = $stmt->fetchAll();
}

// User search
$searchQuery = trim($_GET['q'] ?? '');
$searchResults = [];
if ($searchQuery !== '') {
    $stmt = $pdo->prepare('
        SELECT id, username, avatar FROM users
        WHERE username LIKE ? AND id != ?
        LIMIT 10
    ');
    $stmt->execute(['%' . $searchQuery . '%', $uid]);
    $searchResults = $stmt->fetchAll();

    $friendIds = array_column($friends, 'id');
    foreach ($searchResults as &$r) {
        $r['is_friend']  = in_array((int)$r['id'], array_map('intval', $friendIds));
        $r['is_pending'] = in_array((int)$r['id'], $sentRequestIds);
    }
    unset($r);
}

$colors = genreColors();
$pageTitle  = 'Dashboard - AniTrack';
$activePage = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div style="margin-bottom:28px">
    <h2 style="color:var(--accent);font-size:clamp(26px,4vw,40px);font-weight:800;text-align:left;margin-bottom:4px">
      Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!
    </h2>
    <p style="color:var(--muted)">Here's what's happening in your collection</p>
  </div>

  <div class="stats-grid" style="margin-bottom:32px">
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['total'] ?></div><div class="stat-label">Library Entries</div></div>
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['anime'] ?></div><div class="stat-label">Anime</div></div>
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['manga'] ?></div><div class="stat-label">Manga</div></div>
    <div class="stat-card"><div class="stat-num"><?= (int)$stats['completed'] ?></div><div class="stat-label">Completed</div></div>
    <div class="stat-card"><div class="stat-num"><?= $reviewCount ?></div><div class="stat-label">Reviews</div></div>
    <div class="stat-card"><div class="stat-num"><?= $friendCount ?></div><div class="stat-label">Friends</div></div>
  </div>

  <h3 style="color:var(--accent);font-size:20px;font-weight:800;margin-bottom:14px">Recent Library</h3>
  <?php if ($recentEntries): ?>
    <div class="entry-list" style="margin-bottom:40px">
      <?php foreach ($recentEntries as $e):
        $c = $colors[$e['genre']] ?? ['#7b3ff2cc', '#7b3ff244'];
      ?>
      <div class="entry-card">
        <?php if ($e['image_url']): ?>
          <img class="entry-thumb" src="<?= htmlspecialchars($e['image_url']) ?>" alt="<?= htmlspecialchars($e['title']) ?> cover">
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

  <?php if ($activityFeed): ?>
  <h3 style="color:var(--accent);font-size:20px;font-weight:800;margin-bottom:14px">Friend Activity</h3>
  <div style="background:var(--panel);border:1px solid var(--border);border-radius:10px;padding:8px 20px;margin-bottom:32px">
    <?php foreach ($activityFeed as $i => $a): ?>
    <div style="display:flex;align-items:center;gap:10px;padding:12px 0;<?= $i < count($activityFeed) - 1 ? 'border-bottom:1px solid var(--border)' : '' ?>">
      <?= avatarImg($a['avatar'], $a['username'], 'mini-avatar') ?>
      <div style="flex:1;font-size:14px;line-height:1.5">
        <a href="<?= BASE_URL ?>/pages/user-profile.php?id=<?= $a['uid'] ?>" class="user-link"><?= htmlspecialchars($a['username']) ?></a>
        added
        <a href="<?= BASE_URL ?>/pages/work-detail.php?id=<?= $a['wid'] ?>" style="color:var(--accent);font-weight:600"><?= htmlspecialchars($a['title']) ?></a>
        <span class="tag tag-<?= $a['type'] ?>" style="font-size:10px;vertical-align:middle"><?= ucfirst($a['type']) ?></span>
        <?php if ($a['rating']): ?>
          <span class="rating-badge" style="font-size:12px;margin-left:4px">&#9733; <?= $a['rating'] ?></span>
        <?php endif; ?>
      </div>
      <span style="font-size:12px;color:var(--muted);white-space:nowrap"><?= htmlspecialchars(substr($a['created_at'], 0, 10)) ?></span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if ($pendingRequests): ?>
  <div style="background:var(--panel);border:1px solid var(--accent);border-radius:10px;padding:20px;margin-bottom:28px">
    <h3 style="color:var(--accent);font-size:17px;font-weight:800;margin-bottom:14px">
      Friend Requests <span style="background:var(--accent);color:#fff;border-radius:999px;font-size:11px;padding:1px 8px;margin-left:6px"><?= count($pendingRequests) ?></span>
    </h3>
    <div class="social-list">
      <?php foreach ($pendingRequests as $req): ?>
      <div class="social-user">
        <?= avatarImg($req['avatar'], $req['username'], 'social-avatar') ?>
        <div>
          <a href="<?= BASE_URL ?>/pages/user-profile.php?id=<?= $req['id'] ?>" class="user-link"><?= htmlspecialchars($req['username']) ?></a>
          <p style="font-size:12px;color:var(--muted);margin:0"><?= htmlspecialchars(substr($req['requested_at'], 0, 10)) ?></p>
        </div>
        <div style="display:flex;gap:6px;margin-left:auto">
          <form method="POST" action="<?= BASE_URL ?>/actions/respond-friend.php">
            <?= csrfField() ?>
            <input type="hidden" name="sender_id" value="<?= $req['id'] ?>">
            <input type="hidden" name="action" value="accept">
            <button type="submit" class="btn btn-sm">Accept</button>
          </form>
          <form method="POST" action="<?= BASE_URL ?>/actions/respond-friend.php">
            <?= csrfField() ?>
            <input type="hidden" name="sender_id" value="<?= $req['id'] ?>">
            <input type="hidden" name="action" value="decline">
            <button type="submit" class="btn btn-sm btn-muted">Decline</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="social-panel">
    <div class="social-column">
      <h3>Find Users</h3>
      <form method="GET" action="">
        <div style="display:flex;gap:8px">
          <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search by username..." style="flex:1">
          <button type="submit" class="btn btn-sm">Search</button>
        </div>
      </form>
      <?php if ($searchQuery !== ''): ?>
        <div class="social-list">
          <?php if ($searchResults): ?>
            <?php foreach ($searchResults as $r): ?>
            <div class="social-user">
              <?= avatarImg($r['avatar'], $r['username'], 'social-avatar') ?>
              <div>
                <a href="<?= BASE_URL ?>/pages/user-profile.php?id=<?= $r['id'] ?>" class="user-link"><?= htmlspecialchars($r['username']) ?></a>
              </div>
              <?php if ($r['is_friend']): ?>
                <form method="POST" action="<?= BASE_URL ?>/actions/toggle-friend.php">
                  <?= csrfField() ?>
                  <input type="hidden" name="friend_id" value="<?= $r['id'] ?>">
                  <input type="hidden" name="action" value="remove">
                  <input type="hidden" name="redirect" value="<?= htmlspecialchars(BASE_URL . '/pages/dashboard.php?q=' . urlencode($searchQuery)) ?>">
                  <button type="submit" class="btn btn-sm btn-muted">Friends ✓</button>
                </form>
              <?php elseif ($r['is_pending']): ?>
                <span class="btn btn-sm btn-muted" style="opacity:.7;cursor:default">Pending...</span>
              <?php else: ?>
                <form method="POST" action="<?= BASE_URL ?>/actions/toggle-friend.php">
                  <?= csrfField() ?>
                  <input type="hidden" name="friend_id" value="<?= $r['id'] ?>">
                  <input type="hidden" name="action" value="add">
                  <input type="hidden" name="redirect" value="<?= htmlspecialchars(BASE_URL . '/pages/dashboard.php?q=' . urlencode($searchQuery)) ?>">
                  <button type="submit" class="btn btn-sm">+ Add</button>
                </form>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="color:var(--muted);font-size:14px;margin-top:12px">No users found.</p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="social-column">
      <h3>Friends (<?= $friendCount ?>)</h3>
      <?php if ($friends): ?>
        <div class="social-list">
          <?php foreach ($friends as $f): ?>
          <div class="social-user">
            <?= avatarImg($f['avatar'], $f['username'], 'social-avatar') ?>
            <div>
              <a href="<?= BASE_URL ?>/pages/user-profile.php?id=<?= $f['id'] ?>" class="user-link"><?= htmlspecialchars($f['username']) ?></a>
            </div>
            <a href="<?= BASE_URL ?>/pages/user-profile.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-outline" style="margin-left:auto">View Profile</a>
          </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p style="color:var(--muted);font-size:14px">No friends yet. Search for users above.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
