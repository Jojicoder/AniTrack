<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';

$works = $pdo->query('SELECT * FROM works ORDER BY title')->fetchAll();

$myWorkIds = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT work_id FROM user_library WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $myWorkIds = array_column($stmt->fetchAll(), 'work_id');
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

$pageTitle  = 'Browse Works - AniTrack';
$activePage = 'home';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="page-header">
    <h2>Browse Works</h2>
    <p>Find anime &amp; manga to add to your library</p>
  </div>

  <div class="filter-area">
    <input type="text" id="q" placeholder="Search by title...">
    <select id="type-filter">
      <option value="">All Types</option>
      <option value="anime">Anime</option>
      <option value="manga">Manga</option>
    </select>
    <select id="genre-filter">
      <option value="">All Genres</option>
      <option value="Action">Action</option>
      <option value="Adventure">Adventure</option>
      <option value="Dark Fantasy">Dark Fantasy</option>
      <option value="Historical">Historical</option>
      <option value="Slice of Life">Slice of Life</option>
      <option value="Romance">Romance</option>
      <option value="Sci-Fi">Sci-Fi</option>
      <option value="Sports">Sports</option>
    </select>
    <select id="status-filter">
      <option value="">All Status</option>
      <option value="Completed">Completed</option>
      <option value="Ongoing">Ongoing</option>
      <option value="Airing">Airing</option>
    </select>
  </div>

  <div class="grid" id="works-grid">
    <?php foreach ($works as $w):
      $c          = $colors[$w['genre']] ?? ['#7b3ff2cc', '#7b3ff244'];
      $inLibrary  = in_array($w['id'], $myWorkIds);
      $typeLabel  = $w['type'] === 'anime' ? 'Anime' : 'Manga';
    ?>
    <div class="card"
         data-title="<?= htmlspecialchars($w['title']) ?>"
         data-type="<?= htmlspecialchars($w['type']) ?>"
         data-genre="<?= htmlspecialchars($w['genre']) ?>"
         data-status="<?= htmlspecialchars($w['air_status']) ?>">
      <div class="work-placeholder"
           style="background:linear-gradient(135deg,<?= $c[0] ?>,<?= $c[1] ?>)">
        <span class="wp-type"><?= strtoupper($w['type']) ?></span>
        <span class="wp-title"><?= htmlspecialchars($w['title']) ?></span>
        <?php if ($w['title_jp']): ?>
          <span class="wp-jp"><?= htmlspecialchars($w['title_jp']) ?></span>
        <?php endif; ?>
      </div>
      <div class="card-content">
        <span class="tag tag-<?= $w['type'] ?>"><?= $typeLabel ?></span>
        <span class="tag" style="margin-left:4px"><?= htmlspecialchars($w['genre']) ?></span>
        <h3><?= htmlspecialchars($w['title']) ?></h3>
        <p style="font-size:13px;color:var(--muted)">
          <?= $w['release_year'] ?>
          <?php if ($w['episodes_chapters']): ?>&middot; <?= htmlspecialchars($w['episodes_chapters']) ?><?php endif; ?>
          &middot; <?= htmlspecialchars($w['air_status']) ?>
        </p>
        <a href="<?= BASE_URL ?>/pages/work-detail.php?id=<?= $w['id'] ?>"
           class="btn btn-outline">View Details</a>
        <?php if (isset($_SESSION['user_id'])): ?>
          <?php if ($inLibrary): ?>
            <a href="<?= BASE_URL ?>/pages/library.php"
               class="btn btn-muted" style="margin-top:8px;width:100%">In My Library &#10003;</a>
          <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>/actions/add-to-library.php"
                  style="margin-top:8px">
              <input type="hidden" name="work_id" value="<?= $w['id'] ?>">
              <input type="hidden" name="redirect" value="home">
              <button type="submit" class="btn" style="width:100%">+ Add to My Library</button>
            </form>
          <?php endif; ?>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/auth/login.php"
             class="btn btn-muted" style="margin-top:8px;width:100%">Login to Add</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div id="no-results" class="empty-state" style="display:none">
    <p>No works found.</p>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
const cards = document.querySelectorAll('.card');
const noRes = document.getElementById('no-results');

function filterWorks() {
  const q      = document.getElementById('q').value.toLowerCase();
  const type   = document.getElementById('type-filter').value;
  const genre  = document.getElementById('genre-filter').value;
  const status = document.getElementById('status-filter').value;
  let visible  = 0;

  cards.forEach(c => {
    const match =
      (!q      || c.dataset.title.toLowerCase().includes(q)) &&
      (!type   || c.dataset.type   === type) &&
      (!genre  || c.dataset.genre  === genre) &&
      (!status || c.dataset.status === status);
    c.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  noRes.style.display = visible ? 'none' : 'block';
}

['q','type-filter','genre-filter','status-filter'].forEach(id =>
  document.getElementById(id).addEventListener('input', filterWorks)
);
</script>
