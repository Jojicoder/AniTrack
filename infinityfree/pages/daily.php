<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$dbTitles  = array();
$dbMalIds  = array();
$myWorkIds = array();

try {
    $stmt = $pdo->query('SELECT id, title, type, mal_id FROM works WHERE type = "anime"');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $dbTitles[strtolower($row['title'])] = array(
            'id'    => (int)$row['id'],
            'title' => $row['title'],
            'type'  => $row['type'],
        );
        if (!empty($row['mal_id'])) {
            $dbMalIds['anime:' . (int)$row['mal_id']] = array(
                'id'    => (int)$row['id'],
                'title' => $row['title'],
                'type'  => $row['type'],
            );
        }
    }
} catch (Exception $e) {
    $dbTitles = array();
    $dbMalIds = array();
}

if (isset($_SESSION['user_id'])) {
    csrfToken();
    try {
        $stmt = $pdo->prepare('SELECT work_id FROM user_library WHERE user_id = ?');
        $stmt->execute(array($_SESSION['user_id']));
        $myWorkIds = array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'work_id'));
    } catch (Exception $e) {
        $myWorkIds = array();
    }
}

$daySeed = (int)date('Ymd');
$apiPage = ($daySeed % 8) + 1;
$pickIndex = $daySeed % 25;

$pageTitle  = 'Daily Pick - AniTrack';
$activePage = 'daily';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="page-header" style="margin-bottom:24px">
    <h2>Daily Pick</h2>
    <p>One anime recommendation from MyAnimeList via Jikan API</p>
  </div>

  <div id="daily-status" class="empty-state">
    <p>Loading today's pick...</p>
  </div>

  <div id="daily-pick" style="display:none"></div>

  <p style="font-size:12px;color:var(--muted);margin-top:32px;text-align:center">
    Data from <a href="https://jikan.moe" target="_blank" rel="noopener" style="color:var(--muted)">Jikan API</a>
    (MyAnimeList)
  </p>
</section>

<script>
window.ANITRACK_DAILY = {
  baseUrl: <?= json_encode(BASE_URL) ?>,
  loggedIn: <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>,
  csrfToken: <?= json_encode($_SESSION['csrf_token'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
  dbTitles: <?= json_encode($dbTitles, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
  dbMalIds: <?= json_encode($dbMalIds, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
  myWorkIds: <?= json_encode($myWorkIds) ?>,
  apiPage: <?= $apiPage ?>,
  pickIndex: <?= $pickIndex ?>,
  today: <?= json_encode(date('F j, Y')) ?>
};

(function() {
  var state = window.ANITRACK_DAILY;
  var statusBox = document.getElementById('daily-status');
  var pickBox = document.getElementById('daily-pick');

  function esc(v) {
    return String(v == null ? '' : v)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function truncate(v, max) {
    var t = String(v || '');
    return t.length > max ? t.slice(0, max) + '...' : t;
  }

  function titleKey(t) {
    return String(t || '').toLowerCase();
  }

  function imageFor(item) {
    return (item.images && item.images.jpg && (item.images.jpg.large_image_url || item.images.jpg.image_url)) || '';
  }
  function cleanSynopsis(v) {
    return String(v || '').replace(/\s*\[Written by MAL Rewrite\]\s*/gi, ' ').trim();
  }

  function libraryControls(item, dbWorkId, inLibrary) {
    if (!state.loggedIn) {
      return '<a href="' + esc(state.baseUrl) + '/auth/login.php" class="btn btn-muted">Login to Add</a>';
    }

    if (inLibrary) {
      return '<a href="' + esc(state.baseUrl) + '/pages/library.php" class="btn btn-muted">In My Library &#10003;</a>';
    }

    if (dbWorkId) {
      return [
        '<form method="POST" action="' + esc(state.baseUrl) + '/actions/add-to-library.php">',
        '<input type="hidden" name="csrf_token" value="' + esc(state.csrfToken) + '">',
        '<input type="hidden" name="work_id" value="' + esc(dbWorkId) + '">',
        '<input type="hidden" name="status" value="Plan to Watch">',
        '<input type="hidden" name="redirect" value="daily">',
        '<button type="submit" class="btn">+ Add to My Library</button>',
        '</form>'
      ].join('');
    }

    var genres = Array.isArray(item.genres) && item.genres.length ? item.genres[0].name : '';
    var year = (item.aired && item.aired.prop && item.aired.prop.from && item.aired.prop.from.year)
      ? String(item.aired.prop.from.year) : '';
    var eps = item.episodes ? item.episodes + ' eps' : '';
    return [
      '<form method="POST" action="' + esc(state.baseUrl) + '/actions/import-seasonal.php">',
      '<input type="hidden" name="csrf_token" value="' + esc(state.csrfToken) + '">',
      '<input type="hidden" name="mal_id" value="' + esc(item.mal_id || '') + '">',
      '<input type="hidden" name="title" value="' + esc(item.title || '') + '">',
      '<input type="hidden" name="title_jp" value="' + esc(item.title_japanese || '') + '">',
      '<input type="hidden" name="genre" value="' + esc(genres) + '">',
      '<input type="hidden" name="description" value="' + esc(cleanSynopsis(item.synopsis)) + '">',
      '<input type="hidden" name="image_url" value="' + esc(imageFor(item)) + '">',
      '<input type="hidden" name="release_year" value="' + esc(year) + '">',
      '<input type="hidden" name="episodes_chapters" value="' + esc(eps) + '">',
      '<input type="hidden" name="jikan_status" value="' + esc(item.status || '') + '">',
      '<input type="hidden" name="lib_status" value="Plan to Watch">',
      '<input type="hidden" name="work_type" value="anime">',
      '<input type="hidden" name="redirect" value="daily">',
      '<button type="submit" class="btn">+ Add to My Library</button>',
      '</form>'
    ].join('');
  }

  function render(item) {
    var title = item.title || '';
    var titleJp = item.title_japanese || '';
    var image = imageFor(item);
    var malId = item.mal_id || '';
    var dbWork = state.dbMalIds['anime:' + malId] || state.dbTitles[titleKey(title)];
    var dbWorkId = dbWork ? dbWork.id : null;
    var inLibrary = dbWorkId && state.myWorkIds.indexOf(Number(dbWorkId)) !== -1;
    var genres = Array.isArray(item.genres) ? item.genres.map(function(g) { return g.name; }).filter(Boolean).slice(0, 3) : [];
    var studios = Array.isArray(item.studios) ? item.studios.map(function(s) { return s.name; }).filter(Boolean).slice(0, 2) : [];
    var year = item.year || (item.aired && item.aired.prop && item.aired.prop.from ? item.aired.prop.from.year : '');
    var score = item.score || '';
    var episodes = item.episodes ? item.episodes + ' eps' : '';
    var status = item.status || '';
    var synopsis = cleanSynopsis(item.synopsis);

    var cover = image
      ? '<img src="' + esc(image) + '" alt="' + esc(title) + ' cover" style="width:100%;height:100%;object-fit:cover;display:block">'
      : '<div class="work-placeholder work-placeholder-lg"><span class="wp-type">ANIME</span><span class="wp-title">' + esc(title) + '</span></div>';

    pickBox.innerHTML = [
      '<div class="daily-pick-card">',
      '<div class="daily-pick-layout">',
      '<div class="daily-pick-cover">' + cover + '</div>',
      '<div class="daily-pick-body">',
      '<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">',
      '<span class="tag tag-anime">Anime</span>',
      genres.map(function(g) { return '<span class="tag">' + esc(g) + '</span>'; }).join(''),
      score ? '<span class="rating-badge" style="margin-left:auto">&#9733; ' + esc(score) + ' <small>/ 10</small></span>' : '',
      '</div>',
      '<div>',
      '<p style="font-size:13px;color:var(--muted);font-weight:800;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px">' + esc(state.today) + '</p>',
      '<h2 style="color:var(--text);font-size:clamp(28px,5vw,52px);line-height:1.05;text-align:left;margin:0 0 8px">' + esc(title) + '</h2>',
      titleJp ? '<p class="title-jp" style="margin:0">' + esc(titleJp) + '</p>' : '',
      '</div>',
      '<div class="meta-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));margin:0">',
      '<div class="meta-item"><div class="meta-label">Year</div><div class="meta-value">' + esc(year || '-') + '</div></div>',
      '<div class="meta-item"><div class="meta-label">Episodes</div><div class="meta-value">' + esc(episodes || '-') + '</div></div>',
      '<div class="meta-item"><div class="meta-label">Status</div><div class="meta-value" style="font-size:15px">' + esc(status || '-') + '</div></div>',
      '</div>',
      studios.length ? '<p style="font-size:13px;color:var(--muted);margin:0">Studio: ' + esc(studios.join(', ')) + '</p>' : '',
      synopsis ? '<p class="synopsis" style="margin:0">' + esc(truncate(synopsis, 520)) + '</p>' : '',
      '<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:auto">',
      malId ? '<a href="https://myanimelist.net/anime/' + esc(malId) + '" target="_blank" rel="noopener" class="btn btn-outline">View on MAL</a>' : '',
      dbWorkId ? '<a href="' + esc(state.baseUrl) + '/pages/work-detail.php?id=' + esc(dbWorkId) + '" class="btn btn-outline">AniTrack Details</a>' : '',
      libraryControls(item, dbWorkId, inLibrary),
      '</div>',
      '</div>',
      '</div>',
      '</div>'
    ].join('');
    statusBox.style.display = 'none';
    pickBox.style.display = '';
  }

  function load() {
    var url = 'https://api.jikan.moe/v4/top/anime?limit=25&page=' + state.apiPage + '&filter=bypopularity';
    fetch(url, { headers: { 'Accept': 'application/json' } })
      .then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(payload) {
        var list = Array.isArray(payload.data) ? payload.data : [];
        if (!list.length) throw new Error('empty');
        render(list[state.pickIndex % list.length]);
      })
      .catch(function() {
        statusBox.innerHTML = "<p>Could not load today's pick from Jikan API. Please try again later.</p>";
      });
  }

  load();
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
