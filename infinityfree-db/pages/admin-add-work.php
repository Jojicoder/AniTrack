<?php
session_start();
require_once __DIR__ . '/../config/app.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$messages = [
    'missing'   => 'Please fill in all required fields.',
    'image'     => 'Cover image must be a valid URL.',
    'duplicate' => 'This work is already registered.',
];
$error = $messages[$_GET['error'] ?? ''] ?? '';

$pageTitle = 'Add Work - AniTrack';
$activePage = 'admin';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="page-header">
    <h2>Add Work</h2>
    <p>Search Jikan API and save anime or manga to the database</p>
  </div>

  <div class="form-box">
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div style="margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid rgba(255,255,255,0.07)">
      <label for="api-search" style="display:block;margin-bottom:6px">Search MyAnimeList</label>
      <div style="position:relative">
        <input type="text" id="api-search" placeholder="Type a title to search with Jikan API...">
        <div id="api-results" class="api-results" style="display:none"></div>
      </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>/actions/add-work.php" id="add-work-form">
      <label for="type">Type</label>
      <select id="type" name="type" required>
        <option value="">All types for search</option>
        <option value="anime">Anime</option>
        <option value="manga">Manga</option>
      </select>

      <label for="title">Title</label>
      <input type="text" id="title" name="title" placeholder="e.g. One Piece" required>

      <label for="title-jp">Japanese Title</label>
      <input type="text" id="title-jp" name="title_jp" placeholder="e.g. ワンピース">

      <label for="genre">Genre</label>
      <select id="genre" name="genre" required>
        <option value="">Select genre</option>
        <option>Action</option>
        <option>Adventure</option>
        <option>Dark Fantasy</option>
        <option>Historical</option>
        <option>Slice of Life</option>
        <option>Romance</option>
        <option>Sci-Fi</option>
        <option>Sports</option>
        <option>Fantasy</option>
        <option>Comedy</option>
        <option>Drama</option>
        <option>Horror</option>
        <option>Mystery</option>
      </select>

      <label for="release-year">Release Year</label>
      <input type="number" id="release-year" name="release_year" min="1900" max="2100" required>

      <label for="episodes-chapters">Episodes / Chapters</label>
      <input type="text" id="episodes-chapters" name="episodes_chapters" placeholder="e.g. 24 eps or 1117 ch">

      <label for="air-status">Status</label>
      <select id="air-status" name="air_status" required>
        <option value="">Select status</option>
        <option>Airing</option>
        <option>Ongoing</option>
        <option>Completed</option>
      </select>

      <label for="description">Description</label>
      <textarea id="description" name="description" rows="5" required></textarea>

      <label for="image-url">Cover Image URL</label>
      <input type="url" id="image-url" name="image_url" placeholder="Auto-filled from Jikan API">
      <div id="image-preview" style="display:none;margin-top:8px">
        <img id="image-preview-img" src="" alt="Cover preview" style="width:110px;border-radius:8px;object-fit:cover">
      </div>

      <button type="submit" class="btn">Save Work</button>
    </form>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
const searchInput = document.getElementById('api-search');
const resultsBox = document.getElementById('api-results');
const typeInput = document.getElementById('type');
const imageUrlInput = document.getElementById('image-url');
const imagePreview = document.getElementById('image-preview');
const imagePreviewImg = document.getElementById('image-preview-img');
let searchTimer;

const GENRE_MAP = {
  'Action': 'Action',
  'Adventure': 'Adventure',
  'Award Winning': 'Drama',
  'Comedy': 'Comedy',
  'Drama': 'Drama',
  'Fantasy': 'Fantasy',
  'Horror': 'Horror',
  'Mystery': 'Mystery',
  'Romance': 'Romance',
  'Sci-Fi': 'Sci-Fi',
  'Slice of Life': 'Slice of Life',
  'Sports': 'Sports',
  'Supernatural': 'Fantasy',
  'Suspense': 'Mystery'
};

const STATUS_MAP = {
  'Finished Airing': 'Completed',
  'Currently Airing': 'Airing',
  'Not yet aired': 'Airing',
  'Finished': 'Completed',
  'Publishing': 'Ongoing',
  'On Hiatus': 'Ongoing',
  'Discontinued': 'Completed'
};

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  }[char]));
}

function setPreview(url) {
  imageUrlInput.value = url || '';
  if (url) {
    imagePreviewImg.src = url;
    imagePreview.style.display = 'block';
  } else {
    imagePreview.style.display = 'none';
  }
}

searchInput.addEventListener('input', () => {
  clearTimeout(searchTimer);
  const query = searchInput.value.trim();
  if (query.length < 2) {
    resultsBox.style.display = 'none';
    return;
  }
  searchTimer = setTimeout(() => searchJikan(query), 500);
});

imageUrlInput.addEventListener('input', () => setPreview(imageUrlInput.value.trim()));

async function searchJikan(query) {
  resultsBox.innerHTML = '<div class="api-result-loading">Searching...</div>';
  resultsBox.style.display = 'block';

  try {
    const selectedType = typeInput.value;
    const urls = selectedType === 'anime'
      ? [`https://api.jikan.moe/v4/anime?q=${encodeURIComponent(query)}&limit=6`]
      : selectedType === 'manga'
      ? [`https://api.jikan.moe/v4/manga?q=${encodeURIComponent(query)}&limit=6`]
      : [
          `https://api.jikan.moe/v4/anime?q=${encodeURIComponent(query)}&limit=4`,
          `https://api.jikan.moe/v4/manga?q=${encodeURIComponent(query)}&limit=4`
        ];

    const responses = await Promise.all(urls.map(url => fetch(url).then(response => {
      if (!response.ok) throw new Error('Request failed');
      return response.json();
    })));

    const results = responses.flatMap((response, index) =>
      (response.data || []).map(item => ({
        ...item,
        _kind: urls.length === 1 ? selectedType : (index === 0 ? 'anime' : 'manga')
      }))
    );

    if (!results.length) {
      resultsBox.innerHTML = '<div class="api-result-loading">No results found.</div>';
      return;
    }

    window.jikanResults = results;
    resultsBox.innerHTML = results.map((item, index) => {
      const image = item.images?.jpg?.image_url || '';
      const year = item.year || item.aired?.prop?.from?.year || item.published?.prop?.from?.year || '?';
      return `
        <div class="api-result-item" data-index="${index}">
          <img src="${escapeHtml(image)}" alt="" onerror="this.style.display='none'">
          <div>
            <div class="api-result-title">${escapeHtml(item.title_english || item.title)}</div>
            <div class="api-result-meta">${item._kind.toUpperCase()} &middot; ${year}</div>
          </div>
        </div>`;
    }).join('');

    resultsBox.querySelectorAll('.api-result-item').forEach(item => {
      item.addEventListener('click', () => fillForm(window.jikanResults[Number(item.dataset.index)]));
    });
  } catch (error) {
    resultsBox.innerHTML = '<div class="api-result-loading">Search failed. Check your connection.</div>';
  }
}

function fillForm(item) {
  resultsBox.style.display = 'none';
  searchInput.value = '';

  document.getElementById('type').value = item._kind;
  document.getElementById('title').value = item.title_english || item.title || '';
  document.getElementById('title-jp').value = item.title_japanese || '';

  const genre = (item.genres || []).map(g => g.name).find(name => GENRE_MAP[name]);
  if (genre) document.getElementById('genre').value = GENRE_MAP[genre];

  const year = item.year || item.aired?.prop?.from?.year || item.published?.prop?.from?.year;
  if (year) document.getElementById('release-year').value = year;

  const count = item._kind === 'anime' ? item.episodes : item.chapters;
  document.getElementById('episodes-chapters').value = count ? `${count} ${item._kind === 'anime' ? 'eps' : 'ch'}` : '';

  if (item.status && STATUS_MAP[item.status]) {
    document.getElementById('air-status').value = STATUS_MAP[item.status];
  }

  document.getElementById('description').value = item.synopsis || '';
  setPreview(item.images?.jpg?.large_image_url || item.images?.jpg?.image_url || '');
}

document.addEventListener('click', event => {
  if (!event.target.closest('#api-search') && !event.target.closest('#api-results')) {
    resultsBox.style.display = 'none';
  }
});
</script>
