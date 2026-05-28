<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$dbTitles  = array();
$myWorkIds = array();

try {
    $stmt = $pdo->query('SELECT id, title FROM works WHERE type = "Anime"');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $dbTitles[strtolower($row['title'])] = array(
            'id' => (int)$row['id'],
            'title' => $row['title'],
        );
    }
} catch (Exception $e) {
    $dbTitles = array();
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

$month = (int)date('n');
if ($month <= 3)      $season = 'Winter';
elseif ($month <= 6)  $season = 'Spring';
elseif ($month <= 9)  $season = 'Summer';
else                  $season = 'Fall';
$seasonLabel = $season . ' ' . date('Y');

$pageTitle  = 'Seasonal Anime - AniTrack';
$activePage = 'seasonal';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="page-header" style="margin-bottom:28px">
    <h2>Seasonal Anime</h2>
    <p><span id="season-label"><?= htmlspecialchars($seasonLabel) ?></span> &mdash; <span id="season-count">Loading</span></p>
  </div>

  <div id="seasonal-status" class="empty-state">
    <p>Loading seasonal anime...</p>
  </div>

  <div id="seasonal-grid" class="grid" style="display:none"></div>

  <p style="font-size:12px;color:var(--muted);margin-top:32px;text-align:center">
    Data from <a href="https://jikan.moe" target="_blank" rel="noopener" style="color:var(--muted)">Jikan API</a>
    (MyAnimeList)
  </p>
</section>

<script>
window.ANITRACK_SEASONAL = {
  baseUrl: <?= json_encode(BASE_URL) ?>,
  loggedIn: <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>,
  csrfToken: <?= json_encode($_SESSION['csrf_token'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
  dbTitles: <?= json_encode($dbTitles, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
  myWorkIds: <?= json_encode($myWorkIds) ?>
};

(function() {
  var state = window.ANITRACK_SEASONAL;
  var grid = document.getElementById('seasonal-grid');
  var statusBox = document.getElementById('seasonal-status');
  var countEl = document.getElementById('season-count');

  function esc(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function truncate(value, max) {
    var text = String(value || '');
    return text.length > max ? text.slice(0, max) + '...' : text;
  }

  function titleKey(title) {
    return String(title || '').toLowerCase();
  }

  function imageFor(anime) {
    if (anime.images && anime.images.jpg && anime.images.jpg.large_image_url) {
      return anime.images.jpg.large_image_url;
    }
    if (anime.images && anime.images.jpg && anime.images.jpg.image_url) {
      return anime.images.jpg.image_url;
    }
    return '';
  }

  function libraryControls(dbWorkId, inLibrary) {
    if (!state.loggedIn) {
      return '<a href="' + esc(state.baseUrl) + '/auth/login.php" class="btn btn-muted" style="font-size:13px">Login to Add</a>';
    }

    if (!dbWorkId) {
      return '<span class="btn btn-muted" style="font-size:12px;opacity:.6;cursor:default">Not in AniTrack yet</span>';
    }

    if (inLibrary) {
      return '<a href="' + esc(state.baseUrl) + '/pages/library.php" class="btn btn-muted" style="font-size:13px">In My Library &#10003;</a>';
    }

    return [
      '<form method="POST" action="' + esc(state.baseUrl) + '/actions/add-to-library.php">',
      '<input type="hidden" name="csrf_token" value="' + esc(state.csrfToken) + '">',
      '<input type="hidden" name="work_id" value="' + esc(dbWorkId) + '">',
      '<input type="hidden" name="redirect" value="seasonal">',
      '<button type="submit" class="btn" style="width:100%;font-size:13px">+ Add to My Library</button>',
      '</form>'
    ].join('');
  }

  function renderCard(anime) {
    var title = anime.title || '';
    var titleJp = anime.title_japanese || '';
    var image = imageFor(anime);
    var score = anime.score || '';
    var eps = anime.episodes || '';
    var status = anime.status || '';
    var synopsis = anime.synopsis || '';
    var malId = anime.mal_id || '';
    var genres = Array.isArray(anime.genres) ? anime.genres.map(function(g) { return g.name; }).filter(Boolean).slice(0, 2) : [];
    var dbWork = state.dbTitles[titleKey(title)];
    var dbWorkId = dbWork ? dbWork.id : null;
    var inLibrary = dbWorkId && state.myWorkIds.indexOf(Number(dbWorkId)) !== -1;

    var imageHtml = image
      ? '<img class="work-card-image" src="' + esc(image) + '" alt="' + esc(title) + ' cover" loading="lazy">'
      : '<div class="work-placeholder" style="background:linear-gradient(135deg,#7b3ff2cc,#7b3ff244)"><span class="wp-type">ANIME</span><span class="wp-title">' + esc(title) + '</span></div>';

    return [
      '<div class="card">',
      imageHtml,
      '<div class="card-content">',
      '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:6px">',
      '<span class="tag tag-anime" style="font-size:11px">Anime</span>',
      genres.length ? '<span class="tag" style="font-size:11px">' + esc(genres.join(', ')) + '</span>' : '',
      score ? '<span style="font-size:12px;color:#f59e0b;font-weight:800;margin-left:auto">&#9733; ' + esc(score) + '</span>' : '',
      '</div>',
      '<h3 style="font-size:15px;margin-bottom:2px">' + esc(title) + '</h3>',
      titleJp ? '<p style="font-size:12px;color:var(--muted);margin-bottom:4px">' + esc(titleJp) + '</p>' : '',
      '<p style="font-size:12px;color:var(--muted);margin-bottom:8px">',
      esc(document.getElementById('season-label').textContent),
      eps ? ' &middot; ' + esc(eps) + ' eps' : '',
      status ? ' &middot; ' + esc(status) : '',
      '</p>',
      synopsis ? '<p style="font-size:12px;color:var(--muted);margin-bottom:0;line-height:1.5">' + esc(truncate(synopsis, 120)) + '</p>' : '',
      '<div style="display:flex;flex-direction:column;gap:6px;margin-top:10px">',
      malId ? '<a href="https://myanimelist.net/anime/' + esc(malId) + '" target="_blank" rel="noopener" class="btn btn-outline" style="font-size:13px">View on MAL</a>' : '',
      libraryControls(dbWorkId, inLibrary),
      '</div>',
      '</div>',
      '</div>'
    ].join('');
  }

  fetch('https://api.jikan.moe/v4/seasons/now?limit=25', {
    headers: { 'Accept': 'application/json' }
  })
    .then(function(response) {
      if (!response.ok) throw new Error('HTTP ' + response.status);
      return response.json();
    })
    .then(function(payload) {
      var animeList = Array.isArray(payload.data) ? payload.data : [];
      countEl.textContent = animeList.length + ' titles this season';

      if (!animeList.length) {
        statusBox.innerHTML = '<p>No seasonal data available. Please try again later.</p>';
        return;
      }

      grid.innerHTML = animeList.map(renderCard).join('');
      grid.style.display = '';
      statusBox.style.display = 'none';
    })
    .catch(function() {
      countEl.textContent = 'Unavailable';
      statusBox.innerHTML = '<p>Could not load seasonal data from Jikan API. Please try again later.</p>';
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
