<?php
session_start();
require_once __DIR__ . '/../config/app.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$reviews = $pdo->query('
    SELECT r.*, u.username, w.title as work_title, w.type as work_type
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN works w ON r.work_id = w.id
    ORDER BY r.created_at DESC
')->fetchAll();

$pageTitle  = 'Admin: Reviews - AniTrack';
$activePage = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="page-header">
    <h2>Manage Reviews</h2>
    <p><?= count($reviews) ?> total reviews</p>
  </div>

  <?php if ($reviews): ?>
    <div style="display:grid;gap:14px">
      <?php foreach ($reviews as $r): ?>
      <div class="review-card" style="display:grid;grid-template-columns:1fr auto;gap:16px;align-items:start">
        <div>
          <div class="review-header" style="flex-wrap:wrap;gap:8px">
            <a href="<?= BASE_URL ?>/pages/user-profile.php?id=<?= $r['user_id'] ?>" class="user-link" style="font-weight:800">
              <?= htmlspecialchars($r['username']) ?>
            </a>
            <span style="color:var(--muted)">on</span>
            <a href="<?= BASE_URL ?>/pages/work-detail.php?id=<?= $r['work_id'] ?>" style="color:var(--text);font-weight:700">
              <?= htmlspecialchars($r['work_title']) ?>
            </a>
            <span class="tag tag-<?= $r['work_type'] ?>" style="font-size:11px"><?= ucfirst($r['work_type']) ?></span>
            <span class="rating-badge">&#9733; <?= $r['rating'] ?> <small>/ 10</small></span>
            <span class="review-date"><?= htmlspecialchars(substr($r['created_at'], 0, 10)) ?></span>
          </div>
          <p class="review-text" style="margin-top:8px"><?= nl2br(htmlspecialchars($r['body'])) ?></p>
          <?php if ($r['user_status']): ?>
            <p class="review-status">Status: <?= htmlspecialchars($r['user_status']) ?></p>
          <?php endif; ?>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/actions/delete-review.php"
              onsubmit="return confirm('Delete this review by <?= htmlspecialchars($r['username'], ENT_QUOTES) ?>?')">
          <?= csrfField() ?>
          <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
          <button type="submit" class="btn btn-sm btn-danger">Delete</button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state"><p>No reviews yet.</p></div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
