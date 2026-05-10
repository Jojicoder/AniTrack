'use strict';

const GENRE_COLORS = {
  'Action':       '#7b3ff2',
  'Adventure':    '#1d4ed8',
  'Romance':      '#db2777',
  'Fantasy':      '#d97706',
  'Comedy':       '#16a34a',
  'Horror':       '#dc2626',
  'Slice of Life':'#0891b2',
  'Historical':   '#92400e',
  'Dark Fantasy': '#6b21a8',
  'Drama':        '#475569',
  'Sci-Fi':       '#0369a1',
  'Sports':       '#15803d',
  'Mystery':      '#7c3aed'
};

const SEED_WORKS = [
  {
    id: 1, title: 'Demon Slayer', titleJp: '鬼滅の刃',
    type: 'anime', genre: 'Action', year: 2019, episodes: 44, chapters: null, status: 'Completed',
    synopsis: 'After his family is slaughtered and his sister turned into a demon, Tanjiro Kamado joins the Demon Slayer Corps to avenge his family and find a cure for his sister Nezuko.'
  },
  {
    id: 2, title: 'Attack on Titan', titleJp: '進撃の巨人',
    type: 'anime', genre: 'Action', year: 2013, episodes: 87, chapters: null, status: 'Completed',
    synopsis: 'In a world where humanity lives behind giant walls to protect themselves from Titans, young Eren Yeager vows to exterminate all Titans after his mother is devoured by one.'
  },
  {
    id: 3, title: 'Jujutsu Kaisen', titleJp: '呪術廻戦',
    type: 'anime', genre: 'Action', year: 2020, episodes: 48, chapters: null, status: 'Completed',
    synopsis: 'Yuji Itadori swallows a cursed object to save his classmates and becomes the host of a powerful Curse named Ryomen Sukuna, forcing him into the world of Jujutsu sorcerers.'
  },
  {
    id: 4, title: 'Vinland Saga', titleJp: 'ヴィンランド・サガ',
    type: 'anime', genre: 'Historical', year: 2019, episodes: 48, chapters: null, status: 'Completed',
    synopsis: 'Young Thorfinn grows up in a time of war between England and Denmark. After his father is killed by the mercenary Askeladd, he vows to defeat him in a duel.'
  },
  {
    id: 5, title: 'One Piece', titleJp: 'ワンピース',
    type: 'manga', genre: 'Adventure', year: 1997, episodes: null, chapters: 1117, status: 'Ongoing',
    synopsis: 'Monkey D. Luffy, a boy who gained the properties of rubber after eating a Devil Fruit, sets sail with his crew to find the legendary treasure One Piece and become King of the Pirates.'
  },
  {
    id: 6, title: 'Berserk', titleJp: 'ベルセルク',
    type: 'manga', genre: 'Dark Fantasy', year: 1989, episodes: null, chapters: 374, status: 'Ongoing',
    synopsis: "Guts, a former mercenary, travels the dark medieval world as a lone swordsman haunted by a Brand of Sacrifice, seeking revenge against the man who betrayed him."
  },
  {
    id: 7, title: 'Blue Period', titleJp: 'ブルーピリオド',
    type: 'manga', genre: 'Slice of Life', year: 2017, episodes: null, chapters: 84, status: 'Ongoing',
    synopsis: 'Yatora Yaguchi, a straight-A student, discovers the magic of painting and decides to pursue the prestigious Tokyo University of the Arts despite having no experience.'
  },
  {
    id: 8, title: 'Chainsaw Man', titleJp: 'チェンソーマン',
    type: 'manga', genre: 'Action', year: 2018, episodes: null, chapters: 194, status: 'Ongoing',
    synopsis: 'Denji is a young man burdened with debt who kills devils to pay it off. After merging with his devil-dog Pochita, he gains the power to transform into the Chainsaw Man.'
  }
];

const SEED_USERS = [
  { id: 1, username: 'admin', email: 'admin@anitrack.com', password: 'admin123', role: 'admin', createdAt: '2026-01-01' },
  { id: 2, username: 'testuser', email: 'test@anitrack.com', password: 'test123', role: 'user', createdAt: '2026-03-15' }
];

const SEED_REVIEWS = [
  { id: 1, workId: 1, userId: 2, rating: 9, userStatus: 'Completed', text: 'Beautiful animation and an emotional story. The water breathing techniques look incredible. Mugen Train arc was a masterpiece.', createdAt: '2026-04-01' },
  { id: 2, workId: 6, userId: 2, rating: 10, userStatus: 'Reading', text: "A true masterpiece of the medium. Miura's art is unparalleled and Guts is one of the greatest characters ever written. Essential reading.", createdAt: '2026-04-10' },
  { id: 3, workId: 2, userId: 2, rating: 10, userStatus: 'Completed', text: 'The greatest anime ever made. The story is mind-blowing, the characters are complex, and the ending hit me hard.', createdAt: '2026-04-15' },
  { id: 4, workId: 5, userId: 2, rating: 8, userStatus: 'Reading', text: "One Piece just keeps getting better. The world-building is insane and the emotional moments hit harder than anything else I've read.", createdAt: '2026-04-20' },
  { id: 5, workId: 3, userId: 2, rating: 7, userStatus: 'Completed', text: 'Great action and characters. The animation by MAPPA is stunning. Looking forward to where the story goes next.', createdAt: '2026-04-25' },
  { id: 6, workId: 4, userId: 1, rating: 9, userStatus: 'Completed', text: "Criminally underrated. Thorfinn's development from a vengeful child to a pacifist warrior is one of the best arcs in anime.", createdAt: '2026-05-01' }
];

// ─── Storage ──────────────────────────────────────────────────────────────────
function getWorks() {
  const raw = localStorage.getItem('at_works');
  if (!raw) { localStorage.setItem('at_works', JSON.stringify(SEED_WORKS)); return JSON.parse(JSON.stringify(SEED_WORKS)); }
  return JSON.parse(raw);
}
function saveWorks(d) { localStorage.setItem('at_works', JSON.stringify(d)); }

function getUsers() {
  const raw = localStorage.getItem('at_users');
  if (!raw) { localStorage.setItem('at_users', JSON.stringify(SEED_USERS)); return JSON.parse(JSON.stringify(SEED_USERS)); }
  return JSON.parse(raw);
}
function saveUsers(d) { localStorage.setItem('at_users', JSON.stringify(d)); }

function getReviews() {
  const raw = localStorage.getItem('at_reviews');
  if (!raw) { localStorage.setItem('at_reviews', JSON.stringify(SEED_REVIEWS)); return JSON.parse(JSON.stringify(SEED_REVIEWS)); }
  return JSON.parse(raw);
}
function saveReviews(d) { localStorage.setItem('at_reviews', JSON.stringify(d)); }

function getCurrentUser() {
  const raw = sessionStorage.getItem('at_session');
  return raw ? JSON.parse(raw) : null;
}

// ─── Auth ─────────────────────────────────────────────────────────────────────
function doLogin(email, password) {
  const user = getUsers().find(u => u.email === email && u.password === password);
  if (!user) return { ok: false, msg: 'Incorrect email or password' };
  sessionStorage.setItem('at_session', JSON.stringify({ id: user.id, username: user.username, role: user.role }));
  return { ok: true };
}

function doLogout() {
  sessionStorage.removeItem('at_session');
  window.location.href = 'index.html';
}

function doRegister(username, email, password) {
  const users = getUsers();
  if (users.find(u => u.email === email)) return { ok: false, msg: 'This email address is already in use' };
  if (users.find(u => u.username === username)) return { ok: false, msg: 'This username is already taken' };
  const newUser = {
    id: Date.now(), username: username.trim(), email: email.trim(),
    password, role: 'user', createdAt: new Date().toISOString().split('T')[0]
  };
  users.push(newUser);
  saveUsers(users);
  sessionStorage.setItem('at_session', JSON.stringify({ id: newUser.id, username: newUser.username, role: newUser.role }));
  return { ok: true };
}

function requireAuth() {
  const u = getCurrentUser();
  if (!u) { window.location.href = 'login.html'; return null; }
  return u;
}

function requireAdmin() {
  const u = requireAuth();
  if (u && u.role !== 'admin') { window.location.href = 'index.html'; return null; }
  return u;
}

// ─── Query ────────────────────────────────────────────────────────────────────
function getWorkById(id) { return getWorks().find(w => w.id === Number(id)); }
function getUserById(id) { return getUsers().find(u => u.id === Number(id)); }
function getReviewsByWork(wid) { return getReviews().filter(r => r.workId === Number(wid)); }
function getReviewsByUser(uid) { return getReviews().filter(r => r.userId === Number(uid)); }
function avgRating(reviews) {
  if (!reviews.length) return null;
  return (reviews.reduce((s, r) => s + r.rating, 0) / reviews.length).toFixed(1);
}
function getQueryParam(key) { return new URLSearchParams(window.location.search).get(key); }

// ─── UI Helpers ───────────────────────────────────────────────────────────────
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function workPlaceholder(work) {
  const color = GENRE_COLORS[work.genre] || '#7b3ff2';
  return `<div class="work-placeholder" style="background:linear-gradient(135deg,${color}cc 0%,${color}44 100%)">
    <span class="wp-type">${work.type === 'anime' ? 'ANIME' : 'MANGA'}</span>
    <span class="wp-title">${escHtml(work.title)}</span>
    <span class="wp-jp">${escHtml(work.titleJp)}</span>
  </div>`;
}

function ratingBadge(avg) {
  return avg !== null
    ? `<span class="rating-badge">★ ${avg} <small>/ 10</small></span>`
    : `<span class="rating-none">No reviews</span>`;
}

function showAlert(el, type, msg) {
  el.innerHTML = `<div class="alert alert-${escHtml(type)}">${escHtml(msg)}</div>`;
}

// ─── Nav ─────────────────────────────────────────────────────────────────────
function initNav(active) {
  const u = getCurrentUser();
  const cls = (page) => active === page ? ' class="active"' : '';

  const adminLinks = (u && u.role === 'admin') ? `
    <li class="has-dropdown">
      <a href="admin-add-work.html"${cls('admin')}>Admin ▾</a>
      <ul class="dropdown-menu">
        <li><a href="admin-add-work.html">Add Work</a></li>
        <li><a href="admin-users.html">Manage Users</a></li>
      </ul>
    </li>` : '';

  const authLinks = u ? `
    <li><a href="dashboard.html"${cls('dashboard')}>Dashboard</a></li>
    <li><a href="profile.html"${cls('profile')}>${escHtml(u.username)}</a></li>
    ${adminLinks}
    <li><a href="#" onclick="doLogout();return false;" class="nav-logout">Logout</a></li>
  ` : `
    <li><a href="login.html"${cls('login')}>Login</a></li>
    <li><a href="register.html"${cls('register')}>Register</a></li>
  `;

  document.querySelector('header').innerHTML = `
    <nav class="navbar">
      <a href="index.html" class="logo">AniTrack</a>
      <ul class="nav-links">
        <li><a href="index.html"${cls('home')}>Home</a></li>
        <li><a href="works.html"${cls('works')}>Works</a></li>
        ${authLinks}
      </ul>
    </nav>`;
}
