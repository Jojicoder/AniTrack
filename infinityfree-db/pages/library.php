<?php
session_start();
require_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$stmt = $pdo->prepare('
    SELECT ul.*, w.title, w.title_jp, w.type, w.genre, w.image_url, w.release_year, w.episodes_chapters, w.air_status
    FROM user_library ul
    JOIN works w ON ul.work_id = w.id
    WHERE ul.user_id = ?
    ORDER BY ul.created_at DESC
');
$stmt->execute([$_SESSION['user_id']]);
$entries = $stmt->fetchAll();

$total      = count($entries);
$animeCount = count(array_filter($entries, fn($e) => $e['type'] === 'anime'));
$mangaCount = $total - $animeCount;
$doneCount  = count(array_filter($entries, fn($e) => $e['status'] === 'Completed'));

$statusColors = [
    'Watching'      => '#7b3ff2',
    'Completed'     => '#16a34a',
    'Plan to Watch' => '#0891b2',
    'On Hold'       => '#d97706',
    'Dropped'       => '#dc2626',
    'Reading'       => '#7b3ff2',
    'Plan to Read'  => '#0891b2',
];
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

$pageTitle  = 'My Library - AniTrack';
$activePage = 'library';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:28px">
    <div>
      <h2 style="color:var(--accent);font-size:clamp(26px,4vw,40px);font-weight:800;text-align:left;margin-bottom:4px">
        My Library
      </h2>
      <p style="color:var(--muted);font-size:15px">Your anime &amp; manga collection</p>
    </div>
    <a href="<?= BASE_URL ?>/pages/home.php" class="btn">+ Browse Works</a>
  </div>

  <div class="filter-area">
    <input type="text" id="q" placeholder="Search by title...">
    <select id="type-filter">
      <option value="">All Types</option>
      <option value="anime">Anime</option>
      <option value="manga">Manga</option>
    </select>
    <select id="status-filter">
      <option value="">All Status</option>
      <option value="Watching">Watching</option>
      <option value="Completed">Completed</option>
      <option value="Plan to Watch">Plan to Watch</option>
      <option value="On Hold">On Hold</option>
      <option value="Dropped">Dropped</option>
      <option value="Reading">Reading</option>
      <option value="Plan to Read">Plan to Read</option>
    </select>
  </div>

  <div class="stats-grid" style="margin-bottom:32px">
    <div class="stat-card">
      <div class="stat-num" id="total-count"><?= $total ?></div>
      <div class="stat-label">Total Entries</div>
    </div>
    <div class="stat-card">
      <div class="stat-num" id="anime-count"><?= $animeCount ?></div>
      <div class="stat-label">Anime</div>
    </div>
    <div class="stat-card">
      <div class="stat-num" id="manga-count"><?= $mangaCount ?></div>
      <div class="stat-label">Manga</div>
    </div>
    <div class="stat-card">
      <div class="stat-num" id="completed-count"><?= $doneCount ?></div>
      <div class="stat-label">Completed</div>
    </div>
  </div>

  <?php if (empty($entries)): ?>
    <div class="empty-state">
      <p>Your library is empty.</p>
      <a href="<?= BASE_URL ?>/pages/home.php" class="btn">Browse Works</a>
    </div>
  <?php else: ?>
    <div class="entry-list" id="entry-list">
      <?php foreach ($entries as $e):
        $sc        = $statusColors[$e['status']] ?? '#7b3ff2';
        $typeLabel = strtoupper($e['type']);
        $coverUrl  = $e['image_url'] ?: ($jikanCovers[$e['title']] ?? '');
        $stars     = $e['rating']
          ? str_repeat('&#9733;', (int)$e['rating']) . str_repeat('&#9734;', 10 - (int)$e['rating'])
          : null;
      ?>
      <div class="entry-card"
           data-title="<?= htmlspecialchars($e['title']) ?>"
           data-type="<?= htmlspecialchars($e['type']) ?>"
           data-status="<?= htmlspecialchars($e['status']) ?>">
        <?php if ($coverUrl): ?>
          <img class="entry-thumb"
               src="<?= htmlspecialchars($coverUrl) ?>"
               alt="<?= htmlspecialchars($e['title']) ?> cover">
        <?php else: ?>
          <div class="entry-thumb-placeholder"
               style="background:linear-gradient(135deg,<?= $sc ?>cc,<?= $sc ?>44)">
            <?= $typeLabel ?>
          </div>
        <?php endif; ?>
        <div class="entry-info">
          <h3><?= htmlspecialchars($e['title']) ?></h3>
          <p>
            <span class="tag tag-<?= $e['type'] ?>" style="font-size:11px"><?= ucfirst($e['type']) ?></span>
            &nbsp;
            <span class="tag" style="font-size:11px"><?= htmlspecialchars($e['genre']) ?></span>
          </p>
          <p>Status: <strong><?= htmlspecialchars($e['status']) ?></strong></p>
          <p>
            <?php if ($stars): ?>
              <span class="stars"><?= $stars ?></span>
              <span style="color:var(--muted);font-size:13px"><?= $e['rating'] ?> / 10</span>
            <?php else: ?>
              <span style="color:var(--muted);font-size:13px">Not rated yet</span>
            <?php endif; ?>
          </p>
          <?php if ($e['note']): ?>
            <p style="font-size:13px;font-style:italic;color:var(--muted)">
              &ldquo;<?= htmlspecialchars($e['note']) ?>&rdquo;
            </p>
          <?php endif; ?>
          <div class="entry-actions">
            <a href="<?= BASE_URL ?>/pages/work-detail.php?id=<?= $e['work_id'] ?>"
               class="btn btn-sm btn-outline">View</a>
            <button class="btn btn-sm btn-muted"
                    data-id="<?= $e['id'] ?>"
                    data-status="<?= htmlspecialchars($e['status'], ENT_QUOTES) ?>"
                    data-rating="<?= $e['rating'] ?? '' ?>"
                    data-note="<?= htmlspecialchars($e['note'] ?? '', ENT_QUOTES) ?>"
                    onclick="openEdit(this)">Edit</button>
            <form method="POST" action="<?= BASE_URL ?>/actions/delete-library.php"
                  style="display:inline"
                  onsubmit="return confirm('Remove from library?')">
              <input type="hidden" name="id" value="<?= $e['id'] ?>">
              <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div id="no-results" class="empty-state" style="display:none">
    <p>No entries match your search.</p>
  </div>
</section>

<!-- Edit Modal -->
<div id="edit-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:999;place-items:center">
  <div style="background:var(--panel);border:1px solid var(--border);border-radius:12px;padding:32px;width:min(480px,90%);max-height:90vh;overflow-y:auto">
    <h3 style="color:var(--accent);margin-bottom:20px;font-size:20px">Edit Entry</h3>
    <form method="POST" action="<?= BASE_URL ?>/actions/update-library.php">
      <input type="hidden" name="id" id="edit-id">
      <div style="display:grid;gap:14px">
        <div>
          <label style="color:var(--text);font-weight:800;font-size:15px;display:block;margin-bottom:6px">Status</label>
          <select name="status" id="edit-status">
            <option value="Watching">Watching</option>
            <option value="Completed">Completed</option>
            <option value="Plan to Watch">Plan to Watch</option>
            <option value="On Hold">On Hold</option>
            <option value="Dropped">Dropped</option>
            <option value="Reading">Reading</option>
            <option value="Plan to Read">Plan to Read</option>
          </select>
        </div>
        <div>
          <label style="color:var(--text);font-weight:800;font-size:15px;display:block;margin-bottom:6px">
            Rating (1–10, leave blank for none)
          </label>
          <input type="number" name="rating" id="edit-rating" min="1" max="10" placeholder="e.g. 8">
        </div>
        <div>
          <label style="color:var(--text);font-weight:800;font-size:15px;display:block;margin-bottom:6px">Note</label>
          <textarea name="note" id="edit-note" rows="3" placeholder="Your notes..."></textarea>
        </div>
        <div style="display:flex;gap:10px">
          <button type="submit" class="btn" style="flex:1">Save</button>
          <button type="button" class="btn btn-muted" onclick="closeEdit()" style="flex:1">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
// ── Filter ──
const entries = document.querySelectorAll('.entry-card');
const noRes   = document.getElementById('no-results');

function updateStats(visible) {
  document.getElementById('total-count').textContent     = visible.length;
  document.getElementById('anime-count').textContent     = visible.filter(e => e.dataset.type === 'anime').length;
  document.getElementById('manga-count').textContent     = visible.filter(e => e.dataset.type === 'manga').length;
  document.getElementById('completed-count').textContent = visible.filter(e => e.dataset.status === 'Completed').length;
}

function filterEntries() {
  const q      = document.getElementById('q').value.toLowerCase();
  const type   = document.getElementById('type-filter').value;
  const status = document.getElementById('status-filter').value;
  const visible = [];

  entries.forEach(card => {
    const match =
      (!q      || card.dataset.title.toLowerCase().includes(q)) &&
      (!type   || card.dataset.type   === type) &&
      (!status || card.dataset.status === status);
    card.style.display = match ? '' : 'none';
    if (match) visible.push(card);
  });

  noRes.style.display = visible.length ? 'none' : 'block';
  updateStats(visible);
}

['q','type-filter','status-filter'].forEach(id =>
  document.getElementById(id).addEventListener('input', filterEntries)
);

// ── Edit Modal ──
const modal = document.getElementById('edit-modal');

function openEdit(btn) {
  document.getElementById('edit-id').value     = btn.dataset.id;
  document.getElementById('edit-status').value = btn.dataset.status;
  document.getElementById('edit-rating').value = btn.dataset.rating;
  document.getElementById('edit-note').value   = btn.dataset.note;
  modal.style.display = 'grid';
}

function closeEdit() {
  modal.style.display = 'none';
}

modal.addEventListener('click', function(e) {
  if (e.target === this) closeEdit();
});
</script>
