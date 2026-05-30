<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$dbTitles  = array();
$dbMalIds  = array();
$myWorkIds = array();

try {
    $stmt = $pdo->query('SELECT id, title, type, mal_id FROM works');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $dbTitles[strtolower($row['title'])] = array(
            'id'   => (int)$row['id'],
            'title' => $row['title'],
            'type'  => $row['type'],
        );
        if (!empty($row['mal_id'])) {
            $dbMalIds[$row['type'] . ':' . (int)$row['mal_id']] = array(
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

$pageTitle  = 'Popular - AniTrack';
$activePage = 'popular';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="page-header" style="margin-bottom:24px">
    <h2>Popular Works</h2>
    <p>Top anime and manga from MyAnimeList via Jikan API</p>
  </div>

  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;align-items:center">
    <div style="display:flex;gap:6px">
      <button id="tab-anime" class="btn" onclick="setType('anime')">Anime</button>
      <button id="tab-manga" class="btn btn-outline" onclick="setType('manga')">Manga</button>
    </div>
    <div style="display:flex;gap:6px;margin-left:8px">
      <button id="filter-score" class="btn btn-sm" onclick="setFilter('score')">&#9733; By Score</button>
      <button id="filter-popularity" class="btn btn-sm btn-outline" onclick="setFilter('bypopularity')">&#128081; By Popularity</button>
    </div>
    <span id="popular-count" style="font-size:13px;color:var(--muted);margin-left:auto"></span>
  </div>

  <div id="popular-status" class="empty-state">
    <p>Loading...</p>
  </div>

  <div id="popular-grid" class="grid" style="display:none"></div>

  <p style="font-size:12px;color:var(--muted);margin-top:32px;text-align:center">
    Data from <a href="https://jikan.moe" target="_blank" rel="noopener" style="color:var(--muted)">Jikan API</a>
    (MyAnimeList)
  </p>
</section>

<script>
window.ANITRACK_POPULAR = {
  baseUrl:   <?= json_encode(BASE_URL) ?>,
  loggedIn:  <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>,
  csrfToken: <?= json_encode($_SESSION['csrf_token'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
  dbTitles:  <?= json_encode($dbTitles, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
  dbMalIds:  <?= json_encode($dbMalIds, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
  myWorkIds: <?= json_encode($myWorkIds) ?>
};

(function() {
  var state    = window.ANITRACK_POPULAR;
  var grid     = document.getElementById('popular-grid');
  var statusBox= document.getElementById('popular-status');
  var countEl  = document.getElementById('popular-count');
  var currentType   = 'anime';
  var currentFilter = 'score';

  function esc(v) {
    return String(v == null ? '' : v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
  }
  function truncate(v, max) { var t = String(v||''); return t.length > max ? t.slice(0,max)+'...' : t; }
  function titleKey(t) { return String(t||'').toLowerCase(); }
  function imageFor(item) {
    return (item.images && item.images.jpg && (item.images.jpg.large_image_url || item.images.jpg.image_url)) || '';
  }
  function cleanSynopsis(v) {
    return String(v || '').replace(/\s*\[Written by MAL Rewrite\]\s*/gi, ' ').trim();
  }

  function libraryControls(item, type, dbWorkId, inLibrary) {
    var libStatus = type === 'manga' ? 'Plan to Read' : 'Plan to Watch';
    if (!state.loggedIn) {
      return '<a href="'+esc(state.baseUrl)+'/auth/login.php" class="btn btn-muted" style="font-size:13px">Login to Add</a>';
    }
    if (inLibrary) {
      return '<a href="'+esc(state.baseUrl)+'/pages/library.php" class="btn btn-muted" style="font-size:13px">In My Library &#10003;</a>';
    }
    if (dbWorkId) {
      return [
        '<form method="POST" action="'+esc(state.baseUrl)+'/actions/add-to-library.php">',
        '<input type="hidden" name="csrf_token" value="'+esc(state.csrfToken)+'">',
        '<input type="hidden" name="work_id" value="'+esc(dbWorkId)+'">',
        '<input type="hidden" name="status" value="'+esc(libStatus)+'">',
        '<input type="hidden" name="redirect" value="popular">',
        '<button type="submit" class="btn" style="width:100%;font-size:13px">+ Add to My Library</button>',
        '</form>'
      ].join('');
    }
    var genres = Array.isArray(item.genres) && item.genres.length ? item.genres[0].name : '';
    var year = '';
    if (type === 'anime' && item.aired && item.aired.prop && item.aired.prop.from) year = String(item.aired.prop.from.year || '');
    if (type === 'manga' && item.published && item.published.prop && item.published.prop.from) year = String(item.published.prop.from.year || '');
    var eps = type === 'anime'
      ? (item.episodes ? item.episodes + ' eps' : '')
      : (item.chapters ? item.chapters + ' ch' : '');
    var status = item.status || '';
    var jikanStatus = type === 'anime'
      ? (status === 'Currently Airing' ? 'Airing' : status === 'Finished Airing' ? 'Completed' : 'Completed')
      : (status === 'Publishing' ? 'Ongoing' : 'Completed');
    return [
      '<form method="POST" action="'+esc(state.baseUrl)+'/actions/import-seasonal.php">',
      '<input type="hidden" name="csrf_token" value="'+esc(state.csrfToken)+'">',
      '<input type="hidden" name="mal_id" value="'+esc(item.mal_id||'')+'">',
      '<input type="hidden" name="title" value="'+esc(item.title||'')+'">',
      '<input type="hidden" name="title_jp" value="'+esc(item.title_japanese||'')+'">',
      '<input type="hidden" name="genre" value="'+esc(genres)+'">',
      '<input type="hidden" name="description" value="'+esc(cleanSynopsis(item.synopsis))+'">',
      '<input type="hidden" name="image_url" value="'+esc(imageFor(item))+'">',
      '<input type="hidden" name="release_year" value="'+esc(year)+'">',
      '<input type="hidden" name="episodes_chapters" value="'+esc(eps)+'">',
      '<input type="hidden" name="jikan_status" value="'+esc(jikanStatus)+'">',
      '<input type="hidden" name="lib_status" value="'+esc(libStatus)+'">',
      '<input type="hidden" name="work_type" value="'+esc(type)+'">',
      '<input type="hidden" name="redirect" value="popular">',
      '<button type="submit" class="btn" style="width:100%;font-size:13px">+ Add to My Library</button>',
      '</form>'
    ].join('');
  }

  function renderCard(item, rank, type) {
    var title   = item.title || '';
    var titleJp = item.title_japanese || '';
    var image   = imageFor(item);
    var score   = item.score || '';
    var members = item.members ? item.members.toLocaleString() : '';
    var synopsis= cleanSynopsis(item.synopsis);
    var malId   = item.mal_id || '';
    var genres  = Array.isArray(item.genres) ? item.genres.map(function(g){return g.name;}).filter(Boolean).slice(0,2) : [];
    var dbWork  = state.dbMalIds[type + ':' + malId] || state.dbTitles[titleKey(title)];
    var dbWorkId= (dbWork && dbWork.type === type) ? dbWork.id : null;
    var inLibrary = dbWorkId && state.myWorkIds.indexOf(Number(dbWorkId)) !== -1;
    var malPath = type === 'anime' ? 'anime' : 'manga';

    var imageHtml = image
      ? '<img class="work-card-image" src="'+esc(image)+'" alt="'+esc(title)+' cover" loading="lazy">'
      : '<div class="work-placeholder" style="background:linear-gradient(135deg,#7b3ff2cc,#7b3ff244)"><span class="wp-type">'+esc(type.toUpperCase())+'</span><span class="wp-title">'+esc(title)+'</span></div>';

    return [
      '<div class="card" style="position:relative">',
      '<div style="position:absolute;top:8px;left:8px;background:var(--accent);color:#fff;font-size:12px;font-weight:800;padding:2px 8px;border-radius:6px;z-index:1">#'+rank+'</div>',
      imageHtml,
      '<div class="card-content">',
      '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-bottom:6px">',
      '<span class="tag tag-'+esc(type)+'" style="font-size:11px">'+esc(type.charAt(0).toUpperCase()+type.slice(1))+'</span>',
      genres.length ? '<span class="tag" style="font-size:11px">'+esc(genres.join(', '))+'</span>' : '',
      score ? '<span style="font-size:12px;color:#f59e0b;font-weight:800;margin-left:auto">&#9733; '+esc(score)+'</span>' : '',
      '</div>',
      '<h3 style="font-size:15px;margin-bottom:2px">'+esc(title)+'</h3>',
      titleJp ? '<p style="font-size:12px;color:var(--muted);margin-bottom:4px">'+esc(titleJp)+'</p>' : '',
      members ? '<p style="font-size:12px;color:var(--muted);margin-bottom:8px">'+members+' members</p>' : '',
      synopsis ? '<p style="font-size:12px;color:var(--muted);margin-bottom:0;line-height:1.5">'+esc(truncate(synopsis,120))+'</p>' : '',
      '<div style="display:flex;flex-direction:column;gap:6px;margin-top:10px">',
      malId ? '<a href="https://myanimelist.net/'+esc(malPath)+'/'+esc(malId)+'" target="_blank" rel="noopener" class="btn btn-outline" style="font-size:13px">View on MAL</a>' : '',
      libraryControls(item, type, dbWorkId, inLibrary),
      '</div>',
      '</div>',
      '</div>'
    ].join('');
  }

  function updateTabStyles() {
    document.getElementById('tab-anime').className   = currentType === 'anime' ? 'btn' : 'btn btn-outline';
    document.getElementById('tab-manga').className   = currentType === 'manga' ? 'btn' : 'btn btn-outline';
    document.getElementById('filter-score').className      = currentFilter === 'score'        ? 'btn btn-sm' : 'btn btn-sm btn-outline';
    document.getElementById('filter-popularity').className = currentFilter === 'bypopularity' ? 'btn btn-sm' : 'btn btn-sm btn-outline';
  }

  function load() {
    grid.style.display = 'none';
    statusBox.style.display = '';
    statusBox.innerHTML = '<p>Loading...</p>';
    countEl.textContent = '';
    updateTabStyles();

    var filterParam = currentFilter === 'score' ? '' : '&filter=' + currentFilter;
    var url = 'https://api.jikan.moe/v4/top/' + currentType + '?limit=25' + filterParam;

    fetch(url, { headers: { 'Accept': 'application/json' } })
      .then(function(r) { if (!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(payload) {
        var list = Array.isArray(payload.data) ? payload.data : [];
        countEl.textContent = 'Top ' + list.length + ' ' + currentType;
        if (!list.length) {
          statusBox.innerHTML = '<p>No data available. Please try again later.</p>';
          return;
        }
        grid.innerHTML = list.map(function(item, i) { return renderCard(item, i+1, currentType); }).join('');
        grid.style.display = '';
        statusBox.style.display = 'none';
      })
      .catch(function() {
        countEl.textContent = 'Unavailable';
        statusBox.innerHTML = '<p>Could not load data from Jikan API. Please try again later.</p>';
      });
  }

  window.setType = function(t) { currentType = t; load(); };
  window.setFilter = function(f) { currentFilter = f; load(); };

  load();
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
