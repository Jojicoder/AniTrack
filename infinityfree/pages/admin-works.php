<?php
session_start();
require_once __DIR__ . '/../config/app.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$works = $pdo->query('
    SELECT w.*, COUNT(DISTINCT ul.id) as library_count, COUNT(DISTINCT r.id) as review_count
    FROM works w
    LEFT JOIN user_library ul ON ul.work_id = w.id
    LEFT JOIN reviews r ON r.work_id = w.id
    GROUP BY w.id
    ORDER BY w.title
')->fetchAll();

$pageTitle  = 'Admin: Works - AniTrack';
$activePage = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:28px">
    <div>
      <h2 style="color:var(--accent);font-size:clamp(24px,4vw,38px);font-weight:800;margin-bottom:4px">Manage Works</h2>
      <p style="color:var(--muted)"><?= count($works) ?> works in database</p>
    </div>
    <a href="<?= BASE_URL ?>/pages/admin-add-work.php" class="btn">+ Add Work</a>
  </div>

  <div class="table-wrap">
    <table class="works-table" style="width:100%;border-collapse:collapse;font-size:14px">
      <thead>
        <tr style="border-bottom:2px solid var(--border)">
          <th style="text-align:left;padding:10px;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:1px">Title</th>
          <th style="text-align:center;padding:10px;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:1px">Type</th>
          <th style="text-align:center;padding:10px;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:1px">Year</th>
          <th style="text-align:center;padding:10px;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:1px">Library</th>
          <th style="text-align:center;padding:10px;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:1px">Reviews</th>
          <th style="text-align:right;padding:10px;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:1px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($works as $w): ?>
        <tr style="border-bottom:1px solid var(--border)">
          <td style="padding:12px 10px">
            <div style="font-weight:700;color:var(--text)"><?= htmlspecialchars($w['title']) ?></div>
            <?php if ($w['title_jp']): ?>
              <div style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($w['title_jp']) ?></div>
            <?php endif; ?>
          </td>
          <td style="padding:12px 10px;text-align:center">
            <span class="tag tag-<?= $w['type'] ?>" style="font-size:11px"><?= ucfirst($w['type']) ?></span>
          </td>
          <td style="padding:12px 10px;text-align:center;color:var(--muted)"><?= $w['release_year'] ?? '—' ?></td>
          <td style="padding:12px 10px;text-align:center;color:var(--accent);font-weight:800"><?= $w['library_count'] ?></td>
          <td style="padding:12px 10px;text-align:center;color:var(--accent);font-weight:800"><?= $w['review_count'] ?></td>
          <td style="padding:12px 10px;text-align:right">
            <div style="display:flex;gap:6px;justify-content:flex-end">
              <button class="btn btn-sm btn-outline"
                      onclick="openEdit(<?= htmlspecialchars(json_encode($w), ENT_QUOTES) ?>)">Edit</button>
              <form method="POST" action="<?= BASE_URL ?>/actions/delete-work.php"
                    onsubmit="return confirm('Delete \'<?= htmlspecialchars($w['title'], ENT_QUOTES) ?>\'? This will also remove all library entries and reviews.')">
                <?= csrfField() ?>
                <input type="hidden" name="work_id" value="<?= $w['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Edit Modal -->
<div id="edit-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:999;place-items:center">
  <div style="background:var(--panel);border:1px solid var(--border);border-radius:12px;padding:32px;width:min(560px,92%);max-height:90vh;overflow-y:auto">
    <h3 style="color:var(--accent);margin-bottom:20px;font-size:20px">Edit Work</h3>
    <form method="POST" action="<?= BASE_URL ?>/actions/edit-work.php">
      <?= csrfField() ?>
      <input type="hidden" name="work_id" id="edit-id">
      <div style="display:grid;gap:14px">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div>
            <label style="display:block;margin-bottom:6px;font-weight:700;font-size:13px">Title *</label>
            <input type="text" name="title" id="edit-title" required>
          </div>
          <div>
            <label style="display:block;margin-bottom:6px;font-weight:700;font-size:13px">Japanese Title</label>
            <input type="text" name="title_jp" id="edit-title-jp">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div>
            <label style="display:block;margin-bottom:6px;font-weight:700;font-size:13px">Type *</label>
            <select name="type" id="edit-type" required>
              <option value="anime">Anime</option>
              <option value="manga">Manga</option>
            </select>
          </div>
          <div>
            <label style="display:block;margin-bottom:6px;font-weight:700;font-size:13px">Genre *</label>
            <select name="genre" id="edit-genre" required>
              <option>Action</option><option>Adventure</option><option>Dark Fantasy</option>
              <option>Historical</option><option>Slice of Life</option><option>Romance</option>
              <option>Sci-Fi</option><option>Sports</option><option>Fantasy</option>
              <option>Comedy</option><option>Drama</option><option>Horror</option><option>Mystery</option>
            </select>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
          <div>
            <label style="display:block;margin-bottom:6px;font-weight:700;font-size:13px">Year *</label>
            <input type="number" name="release_year" id="edit-year" min="1900" max="2100" required>
          </div>
          <div>
            <label style="display:block;margin-bottom:6px;font-weight:700;font-size:13px">Episodes/Chapters</label>
            <input type="text" name="episodes_chapters" id="edit-eps">
          </div>
          <div>
            <label style="display:block;margin-bottom:6px;font-weight:700;font-size:13px">Status *</label>
            <select name="air_status" id="edit-status" required>
              <option>Airing</option><option>Ongoing</option><option>Completed</option>
            </select>
          </div>
        </div>

        <div>
          <label style="display:block;margin-bottom:6px;font-weight:700;font-size:13px">Description *</label>
          <textarea name="description" id="edit-desc" rows="4" required></textarea>
        </div>

        <div>
          <label style="display:block;margin-bottom:6px;font-weight:700;font-size:13px">Cover Image URL</label>
          <input type="url" name="image_url" id="edit-image">
        </div>

        <div style="display:flex;gap:10px">
          <button type="submit" class="btn" style="flex:1">Save Changes</button>
          <button type="button" class="btn btn-muted" onclick="closeEdit()" style="flex:1">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
const modal = document.getElementById('edit-modal');

function openEdit(w) {
  document.getElementById('edit-id').value      = w.id;
  document.getElementById('edit-title').value   = w.title;
  document.getElementById('edit-title-jp').value = w.title_jp || '';
  document.getElementById('edit-type').value    = w.type;
  document.getElementById('edit-genre').value   = w.genre;
  document.getElementById('edit-year').value    = w.release_year;
  document.getElementById('edit-eps').value     = w.episodes_chapters || '';
  document.getElementById('edit-status').value  = w.air_status;
  document.getElementById('edit-desc').value    = w.description || '';
  document.getElementById('edit-image').value   = w.image_url || '';
  modal.style.display = 'grid';
}

function closeEdit() { modal.style.display = 'none'; }

modal.addEventListener('click', e => { if (e.target === modal) closeEdit(); });
</script>
