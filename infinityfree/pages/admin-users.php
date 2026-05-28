<?php
session_start();
require_once __DIR__ . '/../config/app.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Invalid request.');
    }
    $targetId = (int)($_POST['target_id'] ?? 0);
    $action   = $_POST['action'] ?? '';

    if ($targetId) {
        if ($action === 'reset_password') {
            $newPassword = $_POST['new_password'] ?? '';
            if (strlen($newPassword) >= 6) {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $targetId]);
                $_SESSION['flash_msg'] = 'Password has been reset successfully.';
            }
        } elseif ($targetId !== (int)$_SESSION['user_id']) {
            if ($action === 'make_admin') {
                $pdo->prepare('UPDATE users SET role = "admin" WHERE id = ?')->execute([$targetId]);
            } elseif ($action === 'make_user') {
                $pdo->prepare('UPDATE users SET role = "user" WHERE id = ?')->execute([$targetId]);
            } elseif ($action === 'delete') {
                $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$targetId]);
            }
        }
    }
    header('Location: ' . BASE_URL . '/pages/admin-users.php');
    exit;
}

$stmt = $pdo->query('
    SELECT u.id, u.username, u.email, u.role, u.avatar, u.created_at,
           COUNT(DISTINCT ul.id) as library_count,
           COUNT(DISTINCT r.id)  as review_count
    FROM users u
    LEFT JOIN user_library ul ON ul.user_id = u.id
    LEFT JOIN reviews r ON r.user_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
');
$users = $stmt->fetchAll();

$pageTitle  = 'Admin: Users - AniTrack';
$activePage = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="page-header">
    <h2>Manage Users</h2>
    <p><?= count($users) ?> registered users</p>
  </div>

  <?php if (isset($_SESSION['flash_msg'])): ?>
    <div class="alert alert-success" style="margin-bottom:20px"><?= htmlspecialchars($_SESSION['flash_msg']) ?></div>
    <?php unset($_SESSION['flash_msg']); ?>
  <?php endif; ?>

  <div class="table-wrap">
    <table class="users-table" style="width:100%;border-collapse:collapse;font-size:14px">
      <thead>
        <tr style="border-bottom:2px solid var(--border)">
          <th style="text-align:left;padding:12px 10px;color:var(--muted);font-size:12px;letter-spacing:1px;text-transform:uppercase">User</th>
          <th style="text-align:left;padding:12px 10px;color:var(--muted);font-size:12px;letter-spacing:1px;text-transform:uppercase">Email</th>
          <th style="text-align:center;padding:12px 10px;color:var(--muted);font-size:12px;letter-spacing:1px;text-transform:uppercase">Role</th>
          <th style="text-align:center;padding:12px 10px;color:var(--muted);font-size:12px;letter-spacing:1px;text-transform:uppercase">Library</th>
          <th style="text-align:center;padding:12px 10px;color:var(--muted);font-size:12px;letter-spacing:1px;text-transform:uppercase">Reviews</th>
          <th style="text-align:left;padding:12px 10px;color:var(--muted);font-size:12px;letter-spacing:1px;text-transform:uppercase">Joined</th>
          <th style="text-align:right;padding:12px 10px;color:var(--muted);font-size:12px;letter-spacing:1px;text-transform:uppercase">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u):
          $isSelf = $u['id'] === (int)$_SESSION['user_id'];
        ?>
        <tr style="border-bottom:1px solid var(--border)">
          <td style="padding:12px 10px">
            <div style="display:flex;align-items:center;gap:10px">
              <?= avatarImg($u['avatar'], $u['username'], 'mini-avatar') ?>
              <a href="<?= BASE_URL ?>/pages/user-profile.php?id=<?= $u['id'] ?>" class="user-link">
                <?= htmlspecialchars($u['username']) ?>
              </a>
              <?php if ($isSelf): ?><span style="color:var(--muted);font-size:11px">(you)</span><?php endif; ?>
            </div>
          </td>
          <td style="padding:12px 10px;color:var(--muted)"><?= htmlspecialchars($u['email']) ?></td>
          <td style="padding:12px 10px;text-align:center">
            <span class="tag <?= $u['role'] === 'admin' ? 'badge-admin' : '' ?>" style="font-size:11px">
              <?= htmlspecialchars($u['role']) ?>
            </span>
          </td>
          <td style="padding:12px 10px;text-align:center;color:var(--accent);font-weight:800"><?= $u['library_count'] ?></td>
          <td style="padding:12px 10px;text-align:center;color:var(--accent);font-weight:800"><?= $u['review_count'] ?></td>
          <td style="padding:12px 10px;color:var(--muted)"><?= htmlspecialchars(substr($u['created_at'], 0, 10)) ?></td>
          <td style="padding:12px 10px;text-align:right">
            <div class="row-actions" style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap">
              <?php if (!$isSelf): ?>
                <?php if ($u['role'] !== 'admin'): ?>
                  <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                    <input type="hidden" name="action" value="make_admin">
                    <button type="submit" class="btn btn-sm btn-outline">Make Admin</button>
                  </form>
                <?php else: ?>
                  <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                    <input type="hidden" name="action" value="make_user">
                    <button type="submit" class="btn btn-sm btn-muted">Remove Admin</button>
                  </form>
                <?php endif; ?>
              <?php endif; ?>
              <form method="POST" id="reset-form-<?= $u['id'] ?>">
                <?= csrfField() ?>
                <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="new_password" id="new-pw-<?= $u['id'] ?>">
                <button type="button" class="btn btn-sm btn-muted"
                  onclick="doResetPw(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')">
                  Reset PW
                </button>
              </form>
              <?php if (!$isSelf): ?>
                <form method="POST" onsubmit="return confirm('Delete <?= htmlspecialchars($u['username'], ENT_QUOTES) ?>? This cannot be undone.')">
                  <?= csrfField() ?>
                  <input type="hidden" name="target_id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="action" value="delete">
                  <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<script>
function doResetPw(id, name) {
  var pw = prompt('New password for ' + name + ':\n(6 characters minimum)');
  if (!pw) return;
  if (pw.length < 6) { alert('At least 6 characters required.'); return; }
  document.getElementById('new-pw-' + id).value = pw;
  document.getElementById('reset-form-' + id).submit();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
