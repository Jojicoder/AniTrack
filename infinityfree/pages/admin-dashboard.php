<?php
session_start();
require_once __DIR__ . '/../config/app.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$userCount   = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$workCount   = (int)$pdo->query('SELECT COUNT(*) FROM works')->fetchColumn();
$reviewCount = (int)$pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
$libraryCount= (int)$pdo->query('SELECT COUNT(*) FROM user_library')->fetchColumn();
$friendCount = (int)$pdo->query('SELECT COUNT(*) FROM friends')->fetchColumn();

$recentUsers = $pdo->query('
    SELECT id, username, email, role, created_at
    FROM users ORDER BY created_at DESC LIMIT 5
')->fetchAll();

$topWorks = $pdo->query('
    SELECT w.title, w.type, COUNT(r.id) as review_count, ROUND(AVG(r.rating),1) as avg_rating
    FROM works w
    LEFT JOIN reviews r ON r.work_id = w.id
    GROUP BY w.id ORDER BY review_count DESC, avg_rating DESC LIMIT 5
')->fetchAll();

$pageTitle  = 'Admin Dashboard - AniTrack';
$activePage = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="page-header">
    <h2>Admin Dashboard</h2>
    <p>Site overview and statistics</p>
  </div>

  <div class="stats-grid" style="margin-bottom:40px">
    <div class="stat-card">
      <div class="stat-num"><?= $userCount ?></div>
      <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $workCount ?></div>
      <div class="stat-label">Works</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $reviewCount ?></div>
      <div class="stat-label">Reviews</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $libraryCount ?></div>
      <div class="stat-label">Library Entries</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= $friendCount ?></div>
      <div class="stat-label">Friendships</div>
    </div>
  </div>

  <div class="admin-2col" style="display:grid;grid-template-columns:1fr 1fr;gap:28px">

    <div>
      <h3 style="color:var(--accent);font-size:18px;font-weight:800;margin-bottom:14px">Recent Users</h3>
      <div style="background:var(--panel);border:1px solid var(--border);border-radius:10px;overflow:hidden">
        <?php foreach ($recentUsers as $u): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--border)">
          <div>
            <a href="<?= BASE_URL ?>/pages/user-profile.php?id=<?= $u['id'] ?>" class="user-link" style="font-weight:700">
              <?= htmlspecialchars($u['username']) ?>
            </a>
            <?php if ($u['role'] === 'admin'): ?>
              <span class="tag badge-admin" style="font-size:10px;margin-left:6px">admin</span>
            <?php endif; ?>
            <div style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($u['email']) ?></div>
          </div>
          <span style="font-size:12px;color:var(--muted)"><?= substr($u['created_at'], 0, 10) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <div style="margin-top:10px">
        <a href="<?= BASE_URL ?>/pages/admin-users.php" class="btn btn-outline btn-sm">All Users &rarr;</a>
      </div>
    </div>

    <div>
      <h3 style="color:var(--accent);font-size:18px;font-weight:800;margin-bottom:14px">Top Reviewed Works</h3>
      <div style="background:var(--panel);border:1px solid var(--border);border-radius:10px;overflow:hidden">
        <?php foreach ($topWorks as $w): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--border)">
          <div>
            <span style="font-weight:700;color:var(--text)"><?= htmlspecialchars($w['title']) ?></span>
            <span class="tag tag-<?= $w['type'] ?>" style="font-size:10px;margin-left:6px"><?= ucfirst($w['type']) ?></span>
          </div>
          <div style="text-align:right;font-size:13px">
            <span style="color:var(--accent);font-weight:800"><?= $w['review_count'] ?> reviews</span>
            <?php if ($w['avg_rating']): ?>
              <span style="color:var(--muted);margin-left:8px">&#9733; <?= $w['avg_rating'] ?></span>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (!$topWorks): ?>
          <div style="padding:16px;color:var(--muted);font-size:14px">No reviews yet.</div>
        <?php endif; ?>
      </div>
      <div style="margin-top:10px">
        <a href="<?= BASE_URL ?>/pages/admin-works.php" class="btn btn-outline btn-sm">All Works &rarr;</a>
      </div>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
