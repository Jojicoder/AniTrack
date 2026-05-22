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
    synopsis: 'After his family is slaughtered and his sister turned into a demon, Tanjiro Kamado joins the Demon Slayer Corps to avenge his family and find a cure for his sister Nezuko.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1286/99889l.jpg'
  },
  {
    id: 2, title: 'Attack on Titan', titleJp: '進撃の巨人',
    type: 'anime', genre: 'Action', year: 2013, episodes: 87, chapters: null, status: 'Completed',
    synopsis: 'In a world where humanity lives behind giant walls to protect themselves from Titans, young Eren Yeager vows to exterminate all Titans after his mother is devoured by one.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/10/47347l.jpg'
  },
  {
    id: 3, title: 'Jujutsu Kaisen', titleJp: '呪術廻戦',
    type: 'anime', genre: 'Action', year: 2020, episodes: 48, chapters: null, status: 'Completed',
    synopsis: 'Yuji Itadori swallows a cursed object to save his classmates and becomes the host of a powerful Curse named Ryomen Sukuna, forcing him into the world of Jujutsu sorcerers.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1171/109222l.jpg'
  },
  {
    id: 4, title: 'Vinland Saga', titleJp: 'ヴィンランド・サガ',
    type: 'anime', genre: 'Historical', year: 2019, episodes: 48, chapters: null, status: 'Completed',
    synopsis: 'Young Thorfinn grows up in a time of war between England and Denmark. After his father is killed by the mercenary Askeladd, he vows to defeat him in a duel.',
    streamUrl: 'https://www.netflix.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1500/103005l.jpg'
  },
  {
    id: 5, title: 'One Piece', titleJp: 'ワンピース',
    type: 'manga', genre: 'Adventure', year: 1997, episodes: null, chapters: 1117, status: 'Ongoing',
    synopsis: 'Monkey D. Luffy, a boy who gained the properties of rubber after eating a Devil Fruit, sets sail with his crew to find the legendary treasure One Piece and become King of the Pirates.',
    streamUrl: null, buyUrl: 'https://www.amazon.co.jp', imageUrl: 'https://cdn.myanimelist.net/images/manga/2/253146l.jpg'
  },
  {
    id: 6, title: 'Berserk', titleJp: 'ベルセルク',
    type: 'manga', genre: 'Dark Fantasy', year: 1989, episodes: null, chapters: 374, status: 'Ongoing',
    synopsis: "Guts, a former mercenary, travels the dark medieval world as a lone swordsman haunted by a Brand of Sacrifice, seeking revenge against the man who betrayed him.",
    streamUrl: null, buyUrl: 'https://www.amazon.co.jp', imageUrl: 'https://cdn.myanimelist.net/images/manga/1/157897l.jpg'
  },
  {
    id: 7, title: 'Blue Period', titleJp: 'ブルーピリオド',
    type: 'manga', genre: 'Slice of Life', year: 2017, episodes: null, chapters: 84, status: 'Ongoing',
    synopsis: 'Yatora Yaguchi, a straight-A student, discovers the magic of painting and decides to pursue the prestigious Tokyo University of the Arts despite having no experience.',
    streamUrl: null, buyUrl: 'https://www.amazon.co.jp', imageUrl: 'https://cdn.myanimelist.net/images/manga/2/204827l.jpg'
  },
  {
    id: 8, title: 'Chainsaw Man', titleJp: 'チェンソーマン',
    type: 'manga', genre: 'Action', year: 2018, episodes: null, chapters: 194, status: 'Ongoing',
    synopsis: 'Denji is a young man burdened with debt who kills devils to pay it off. After merging with his devil-dog Pochita, he gains the power to transform into the Chainsaw Man.',
    streamUrl: null, buyUrl: 'https://www.amazon.co.jp', imageUrl: 'https://cdn.myanimelist.net/images/manga/3/216464l.jpg'
  },
  {
    id: 9, title: 'Fullmetal Alchemist: Brotherhood', titleJp: '鋼の錬金術師 FULLMETAL ALCHEMIST',
    type: 'anime', genre: 'Adventure', year: 2009, episodes: 64, chapters: null, status: 'Completed',
    synopsis: 'Brothers Edward and Alphonse Elric search for the Philosopher\'s Stone after a failed alchemy experiment costs them their bodies and pulls them into a conspiracy that threatens their world.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1208/94745l.jpg'
  },
  {
    id: 10, title: 'Death Note', titleJp: 'デスノート',
    type: 'anime', genre: 'Mystery', year: 2006, episodes: 37, chapters: null, status: 'Completed',
    synopsis: 'Light Yagami finds a notebook that can kill anyone whose name is written in it, starting a tense battle of intellect against the detective known as L.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1079/138100l.jpg'
  },
  {
    id: 11, title: 'Spy x Family', titleJp: 'SPY×FAMILY',
    type: 'anime', genre: 'Comedy', year: 2022, episodes: 12, chapters: null, status: 'Completed',
    synopsis: 'A spy, an assassin, and a telepath form a fake family for a secret mission, but each hides their real identity from the others.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1441/122795l.jpg'
  },
  {
    id: 12, title: "Frieren: Beyond Journey's End", titleJp: '葬送のフリーレン',
    type: 'anime', genre: 'Fantasy', year: 2023, episodes: 28, chapters: null, status: 'Completed',
    synopsis: 'After defeating the Demon King, the long-lived elf mage Frieren begins a new journey to understand the human bonds she once took for granted.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1015/138006l.jpg'
  },
  {
    id: 13, title: 'My Hero Academia', titleJp: '僕のヒーローアカデミア',
    type: 'anime', genre: 'Action', year: 2016, episodes: 13, chapters: null, status: 'Completed',
    synopsis: 'Izuku Midoriya is born without powers in a world full of heroes, but a chance meeting with All Might gives him the opportunity to train at U.A. High.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/10/78745l.jpg'
  },
  {
    id: 14, title: 'Naruto', titleJp: 'ナルト',
    type: 'anime', genre: 'Adventure', year: 2002, episodes: 220, chapters: null, status: 'Completed',
    synopsis: 'Naruto Uzumaki, a mischievous young ninja shunned by his village, trains to become Hokage and earn the respect of everyone around him.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1141/142503l.jpg'
  },
  {
    id: 15, title: 'Haikyu!!', titleJp: 'ハイキュー!!',
    type: 'anime', genre: 'Sports', year: 2014, episodes: 25, chapters: null, status: 'Completed',
    synopsis: 'Shoyo Hinata joins Karasuno High School\'s volleyball team and learns what it takes to compete alongside and against talented rivals.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/7/76014l.jpg'
  },
  {
    id: 16, title: 'Your Name.', titleJp: '君の名は。',
    type: 'anime', genre: 'Drama', year: 2016, episodes: 1, chapters: null, status: 'Completed',
    synopsis: 'Two teenagers living separate lives mysteriously begin swapping bodies, setting them on a search for one another across distance and time.',
    streamUrl: null, buyUrl: 'https://www.amazon.co.jp', imageUrl: 'https://cdn.myanimelist.net/images/anime/5/87048l.jpg'
  },
  {
    id: 17, title: 'Cowboy Bebop', titleJp: 'カウボーイビバップ',
    type: 'anime', genre: 'Sci-Fi', year: 1998, episodes: 26, chapters: null, status: 'Completed',
    synopsis: 'A crew of bounty hunters travels through space chasing criminals while each member carries unfinished business from the past.',
    streamUrl: 'https://www.netflix.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/4/19644l.jpg'
  },
  {
    id: 18, title: 'Steins;Gate', titleJp: 'STEINS;GATE',
    type: 'anime', genre: 'Sci-Fi', year: 2011, episodes: 24, chapters: null, status: 'Completed',
    synopsis: 'A self-proclaimed mad scientist and his friends accidentally discover a way to send messages to the past, pulling them into a dangerous fight over time itself.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1935/127974l.jpg'
  },
  {
    id: 19, title: 'Hunter x Hunter', titleJp: 'HUNTER×HUNTER',
    type: 'anime', genre: 'Adventure', year: 2011, episodes: 148, chapters: null, status: 'Completed',
    synopsis: 'Gon Freecss becomes a Hunter to search for his father, meeting friends and rivals through exams, battles, and dangerous journeys.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1337/99013l.jpg'
  },
  {
    id: 20, title: 'Code Geass: Lelouch of the Rebellion', titleJp: 'コードギアス 反逆のルルーシュ',
    type: 'anime', genre: 'Sci-Fi', year: 2006, episodes: 25, chapters: null, status: 'Completed',
    synopsis: 'Lelouch gains the power to command others and leads a rebellion against the Holy Britannian Empire while hiding behind the masked identity Zero.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/5/50331l.jpg'
  },
  {
    id: 21, title: 'One Punch Man', titleJp: 'ワンパンマン',
    type: 'anime', genre: 'Action', year: 2015, episodes: 12, chapters: null, status: 'Completed',
    synopsis: 'Saitama is a hero who can defeat any enemy with one punch, leaving him bored as he searches for a challenge worthy of his strength.',
    streamUrl: 'https://www.netflix.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/12/76049l.jpg'
  },
  {
    id: 22, title: 'Mob Psycho 100', titleJp: 'モブサイコ100',
    type: 'anime', genre: 'Action', year: 2016, episodes: 12, chapters: null, status: 'Completed',
    synopsis: 'Shigeo Kageyama is a quiet middle schooler with overwhelming psychic powers, trying to grow as a person without relying on his abilities.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/8/80356l.jpg'
  },
  {
    id: 23, title: 'Violet Evergarden', titleJp: 'ヴァイオレット・エヴァーガーデン',
    type: 'anime', genre: 'Drama', year: 2018, episodes: 13, chapters: null, status: 'Completed',
    synopsis: 'A former child soldier becomes an Auto Memory Doll, writing letters for others while learning the meaning of emotions and love.',
    streamUrl: 'https://www.netflix.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1795/95088l.jpg'
  },
  {
    id: 24, title: 'Kaguya-sama: Love is War', titleJp: 'かぐや様は告らせたい',
    type: 'anime', genre: 'Romance', year: 2019, episodes: 12, chapters: null, status: 'Completed',
    synopsis: 'Two brilliant student council leaders are in love, but both refuse to confess first and turn romance into a battle of strategy.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1295/106551l.jpg'
  },
  {
    id: 25, title: 'Cyberpunk: Edgerunners', titleJp: 'サイバーパンク エッジランナーズ',
    type: 'anime', genre: 'Sci-Fi', year: 2022, episodes: 10, chapters: null, status: 'Completed',
    synopsis: 'In Night City, a street kid becomes an edgerunner and risks everything for survival, freedom, and the people he cares about.',
    streamUrl: 'https://www.netflix.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1818/126435l.jpg'
  },
  {
    id: 26, title: 'Bocchi the Rock!', titleJp: 'ぼっち・ざ・ろっく！',
    type: 'anime', genre: 'Comedy', year: 2022, episodes: 12, chapters: null, status: 'Completed',
    synopsis: 'A socially anxious guitarist joins a band and slowly learns to connect with others through music and live performances.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1448/127956l.jpg'
  },
  {
    id: 27, title: 'Tokyo Ghoul', titleJp: '東京喰種トーキョーグール',
    type: 'anime', genre: 'Horror', year: 2014, episodes: 12, chapters: null, status: 'Completed',
    synopsis: 'Ken Kaneki becomes half-ghoul after a deadly encounter and is forced to survive between human society and the hidden world of ghouls.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/1498/134443l.jpg'
  },
  {
    id: 28, title: 'Sword Art Online', titleJp: 'ソードアート・オンライン',
    type: 'anime', genre: 'Fantasy', year: 2012, episodes: 25, chapters: null, status: 'Completed',
    synopsis: 'Players trapped inside a virtual reality MMORPG must clear the game to escape, with death in the game meaning death in real life.',
    streamUrl: 'https://www.crunchyroll.com', buyUrl: null, imageUrl: 'https://cdn.myanimelist.net/images/anime/11/39717l.jpg'
  }
];

const SEED_USERS = [
  { id: 1, username: 'admin', email: 'admin@anitrack.com', password: 'admin123', role: 'admin', createdAt: '2026-01-01' },
  { id: 2, username: 'testuser', email: 'test@anitrack.com', password: 'test123', role: 'user', createdAt: '2026-03-15' },
  { id: 3, username: 'yuki', email: 'yuki@anitrack.com', password: 'yuki123', role: 'user', createdAt: '2026-04-02' },
  { id: 4, username: 'ken', email: 'ken@anitrack.com', password: 'ken123', role: 'user', createdAt: '2026-04-09' },
  { id: 5, username: 'mia', email: 'mia@anitrack.com', password: 'mia123', role: 'user', createdAt: '2026-04-18' },
  { id: 6, username: 'sora', email: 'sora@anitrack.com', password: 'sora123', role: 'user', createdAt: '2026-05-03' }
];

const SEED_REVIEWS = [
  { id: 1, workId: 1, userId: 2, rating: 9, userStatus: 'Completed', text: 'Beautiful animation and an emotional story. The water breathing techniques look incredible. Mugen Train arc was a masterpiece.', createdAt: '2026-04-01' },
  { id: 2, workId: 6, userId: 2, rating: 10, userStatus: 'Reading', text: "A true masterpiece of the medium. Miura's art is unparalleled and Guts is one of the greatest characters ever written. Essential reading.", createdAt: '2026-04-10' },
  { id: 3, workId: 2, userId: 2, rating: 10, userStatus: 'Completed', text: 'The greatest anime ever made. The story is mind-blowing, the characters are complex, and the ending hit me hard.', createdAt: '2026-04-15' },
  { id: 4, workId: 5, userId: 2, rating: 8, userStatus: 'Reading', text: "One Piece just keeps getting better. The world-building is insane and the emotional moments hit harder than anything else I've read.", createdAt: '2026-04-20' },
  { id: 5, workId: 3, userId: 2, rating: 7, userStatus: 'Completed', text: 'Great action and characters. The animation by MAPPA is stunning. Looking forward to where the story goes next.', createdAt: '2026-04-25' },
  { id: 6, workId: 4, userId: 1, rating: 9, userStatus: 'Completed', text: "Criminally underrated. Thorfinn's development from a vengeful child to a pacifist warrior is one of the best arcs in anime.", createdAt: '2026-05-01' },
  { id: 7, workId: 12, userId: 3, rating: 10, userStatus: 'Completed', text: 'Frieren is quiet but powerful. The way it treats time, memory, and friendship makes every episode feel meaningful.', createdAt: '2026-05-04' },
  { id: 8, workId: 18, userId: 3, rating: 9, userStatus: 'Completed', text: 'The first half is slow in a good way, then the payoff is excellent. One of the best sci-fi stories here.', createdAt: '2026-05-06' },
  { id: 9, workId: 15, userId: 4, rating: 9, userStatus: 'Completed', text: 'Haikyu makes every match feel important. The teamwork and character growth are easy to root for.', createdAt: '2026-05-07' },
  { id: 10, workId: 21, userId: 4, rating: 8, userStatus: 'Completed', text: 'Fast, funny, and stylish. Saitama is hilarious, but the side characters make it even better.', createdAt: '2026-05-09' },
  { id: 11, workId: 24, userId: 5, rating: 9, userStatus: 'Completed', text: 'The comedy is sharp and the romance actually develops. The narrator makes every small moment dramatic.', createdAt: '2026-05-10' },
  { id: 12, workId: 23, userId: 5, rating: 10, userStatus: 'Completed', text: 'Beautiful animation and a strong emotional core. Violet learning how to understand people really works.', createdAt: '2026-05-12' },
  { id: 13, workId: 17, userId: 6, rating: 10, userStatus: 'Completed', text: 'Cowboy Bebop still feels cool and different. The music, atmosphere, and ending are unforgettable.', createdAt: '2026-05-13' },
  { id: 14, workId: 25, userId: 6, rating: 8, userStatus: 'Completed', text: 'Short, intense, and visually loud in the best way. The ending hits hard.', createdAt: '2026-05-14' },
  { id: 15, workId: 19, userId: 3, rating: 9, userStatus: 'Watching', text: 'Hunter x Hunter keeps changing shape. The arcs feel different but the adventure stays strong.', createdAt: '2026-05-15' },
  { id: 16, workId: 20, userId: 4, rating: 9, userStatus: 'Completed', text: 'Lelouch makes every episode feel like a chess match. Very dramatic and easy to binge.', createdAt: '2026-05-16' }
];

const SEED_LIBRARY = [
  { id: 1001, userId: 3, workId: 12, status: 'Completed', rating: 10, note: 'A favorite comfort anime.', createdAt: '2026-05-04' },
  { id: 1002, userId: 3, workId: 18, status: 'Completed', rating: 9, note: 'Great time travel story.', createdAt: '2026-05-06' },
  { id: 1003, userId: 3, workId: 19, status: 'Watching', rating: 9, note: 'Currently on Chimera Ant arc.', createdAt: '2026-05-15' },
  { id: 1004, userId: 4, workId: 15, status: 'Completed', rating: 9, note: 'Best sports anime energy.', createdAt: '2026-05-07' },
  { id: 1005, userId: 4, workId: 21, status: 'Completed', rating: 8, note: 'Great action comedy.', createdAt: '2026-05-09' },
  { id: 1006, userId: 4, workId: 20, status: 'Completed', rating: 9, note: 'Zero is iconic.', createdAt: '2026-05-16' },
  { id: 1007, userId: 5, workId: 24, status: 'Completed', rating: 9, note: 'Very funny romance.', createdAt: '2026-05-10' },
  { id: 1008, userId: 5, workId: 23, status: 'Completed', rating: 10, note: 'Beautiful and emotional.', createdAt: '2026-05-12' },
  { id: 1009, userId: 5, workId: 16, status: 'Completed', rating: 9, note: 'Still looks amazing.', createdAt: '2026-05-13' },
  { id: 1010, userId: 6, workId: 17, status: 'Completed', rating: 10, note: 'Classic space western.', createdAt: '2026-05-13' },
  { id: 1011, userId: 6, workId: 25, status: 'Completed', rating: 8, note: 'Stylish and tragic.', createdAt: '2026-05-14' },
  { id: 1012, userId: 6, workId: 28, status: 'Plan to Watch', rating: null, note: '', createdAt: '2026-05-17' }
];

const SEED_REVIEW_REPLIES = [
  { id: 2001, reviewId: 7, userId: 5, text: 'I agree. It feels slow at first, but the emotional payoff is really strong.', createdAt: '2026-05-05' },
  { id: 2002, reviewId: 9, userId: 4, text: 'The final rallies always get me. Karasuno has such good chemistry.', createdAt: '2026-05-08' },
  { id: 2003, reviewId: 13, userId: 3, text: 'The soundtrack alone makes Cowboy Bebop worth watching.', createdAt: '2026-05-14' },
  { id: 2004, reviewId: 11, userId: 6, text: 'The mind games are what make the romance work so well.', createdAt: '2026-05-11' }
];

const SEED_FRIENDS = [
  { id: 3001, userId: 2, friendId: 3, createdAt: '2026-05-10' },
  { id: 3002, userId: 2, friendId: 5, createdAt: '2026-05-11' },
  { id: 3003, userId: 3, friendId: 5, createdAt: '2026-05-12' },
  { id: 3004, userId: 4, friendId: 6, createdAt: '2026-05-13' },
  { id: 3005, userId: 5, friendId: 3, createdAt: '2026-05-14' },
  { id: 3006, userId: 6, friendId: 4, createdAt: '2026-05-15' }
];

// ─── Storage ──────────────────────────────────────────────────────────────────
const WORKS_VER = 6;
const USERS_VER = 2;
const REVIEWS_VER = 2;
const LIBRARY_VER = 1;
const REVIEW_REPLIES_VER = 1;
const FRIENDS_VER = 1;

function cloneSeedWorks() {
  return JSON.parse(JSON.stringify(SEED_WORKS));
}

function normalizeTitle(title) {
  return String(title || '').trim().toLowerCase();
}

function mergeSeedWorks(works) {
  const merged = Array.isArray(works) ? [...works] : [];
  SEED_WORKS.forEach(seed => {
    const existing = merged.find(work => work.id === seed.id || normalizeTitle(work.title) === normalizeTitle(seed.title));
    if (existing) {
      if (!existing.imageUrl && seed.imageUrl) existing.imageUrl = seed.imageUrl;
      if (!existing.synopsis && seed.synopsis) existing.synopsis = seed.synopsis;
      return;
    }
    merged.push(JSON.parse(JSON.stringify(seed)));
  });
  return merged;
}

function mergeById(current, seeds) {
  const merged = Array.isArray(current) ? [...current] : [];
  seeds.forEach(seed => {
    const existing = merged.find(item => item.id === seed.id);
    if (!existing) merged.push(JSON.parse(JSON.stringify(seed)));
  });
  return merged;
}

function mergeUsers(current) {
  const merged = Array.isArray(current) ? [...current] : [];
  SEED_USERS.forEach(seed => {
    const existing = merged.find(user => user.id === seed.id || user.email.toLowerCase() === seed.email.toLowerCase());
    if (!existing) merged.push(JSON.parse(JSON.stringify(seed)));
  });
  return merged;
}

function mergeLibraryEntries(current) {
  const merged = Array.isArray(current) ? [...current] : [];
  SEED_LIBRARY.forEach(seed => {
    const existing = merged.find(entry => entry.userId === seed.userId && entry.workId === seed.workId);
    if (!existing) merged.push(JSON.parse(JSON.stringify(seed)));
  });
  return merged;
}

function mergeFriends(current) {
  const merged = Array.isArray(current) ? [...current] : [];
  SEED_FRIENDS.forEach(seed => {
    const existing = merged.find(friend => friend.userId === seed.userId && friend.friendId === seed.friendId);
    if (!existing) merged.push(JSON.parse(JSON.stringify(seed)));
  });
  return merged;
}

function getWorks() {
  const raw = localStorage.getItem('at_works');
  const ver = Number(localStorage.getItem('at_works_ver') || 0);
  if (!raw) {
    const fresh = cloneSeedWorks();
    localStorage.setItem('at_works', JSON.stringify(fresh));
    localStorage.setItem('at_works_ver', String(WORKS_VER));
    return fresh;
  }

  let works;
  try {
    works = JSON.parse(raw);
  } catch {
    works = cloneSeedWorks();
  }

  if (ver < WORKS_VER) {
    works = mergeSeedWorks(works);
    localStorage.setItem('at_works', JSON.stringify(works));
    localStorage.setItem('at_works_ver', String(WORKS_VER));
  }
  return works;
}
function saveWorks(d) {
  localStorage.setItem('at_works', JSON.stringify(d));
  localStorage.setItem('at_works_ver', String(WORKS_VER));
}

function getUsers() {
  const raw = localStorage.getItem('at_users');
  const ver = Number(localStorage.getItem('at_users_ver') || 0);
  if (!raw) {
    const fresh = JSON.parse(JSON.stringify(SEED_USERS));
    localStorage.setItem('at_users', JSON.stringify(fresh));
    localStorage.setItem('at_users_ver', String(USERS_VER));
    return fresh;
  }
  let users = JSON.parse(raw);
  if (ver < USERS_VER) {
    users = mergeUsers(users);
    localStorage.setItem('at_users', JSON.stringify(users));
    localStorage.setItem('at_users_ver', String(USERS_VER));
  }
  return users;
}
function saveUsers(d) {
  localStorage.setItem('at_users', JSON.stringify(d));
  localStorage.setItem('at_users_ver', String(USERS_VER));
}

function getReviews() {
  const raw = localStorage.getItem('at_reviews');
  const ver = Number(localStorage.getItem('at_reviews_ver') || 0);
  if (!raw) {
    const fresh = JSON.parse(JSON.stringify(SEED_REVIEWS));
    localStorage.setItem('at_reviews', JSON.stringify(fresh));
    localStorage.setItem('at_reviews_ver', String(REVIEWS_VER));
    return fresh;
  }
  let reviews = JSON.parse(raw);
  if (ver < REVIEWS_VER) {
    reviews = mergeById(reviews, SEED_REVIEWS);
    localStorage.setItem('at_reviews', JSON.stringify(reviews));
    localStorage.setItem('at_reviews_ver', String(REVIEWS_VER));
  }
  return reviews;
}
function saveReviews(d) {
  localStorage.setItem('at_reviews', JSON.stringify(d));
  localStorage.setItem('at_reviews_ver', String(REVIEWS_VER));
}

function getReviewReplies() {
  const raw = localStorage.getItem('at_review_replies');
  const ver = Number(localStorage.getItem('at_review_replies_ver') || 0);
  if (!raw) {
    const fresh = JSON.parse(JSON.stringify(SEED_REVIEW_REPLIES));
    localStorage.setItem('at_review_replies', JSON.stringify(fresh));
    localStorage.setItem('at_review_replies_ver', String(REVIEW_REPLIES_VER));
    return fresh;
  }
  let replies = JSON.parse(raw);
  if (ver < REVIEW_REPLIES_VER) {
    replies = mergeById(replies, SEED_REVIEW_REPLIES);
    localStorage.setItem('at_review_replies', JSON.stringify(replies));
    localStorage.setItem('at_review_replies_ver', String(REVIEW_REPLIES_VER));
  }
  return replies;
}
function saveReviewReplies(d) {
  localStorage.setItem('at_review_replies', JSON.stringify(d));
  localStorage.setItem('at_review_replies_ver', String(REVIEW_REPLIES_VER));
}
function getRepliesByReview(reviewId) {
  return getReviewReplies().filter(reply => reply.reviewId === Number(reviewId));
}
function saveReviewReply(reply) {
  const replies = getReviewReplies();
  replies.push({
    id: Date.now(),
    reviewId: Number(reply.reviewId),
    userId: Number(reply.userId),
    text: reply.text,
    createdAt: new Date().toISOString().split('T')[0]
  });
  saveReviewReplies(replies);
}

function getLibrary() {
  const raw = localStorage.getItem('at_library');
  const ver = Number(localStorage.getItem('at_library_ver') || 0);
  if (!raw) {
    const fresh = JSON.parse(JSON.stringify(SEED_LIBRARY));
    localStorage.setItem('at_library', JSON.stringify(fresh));
    localStorage.setItem('at_library_ver', String(LIBRARY_VER));
    return fresh;
  }
  let library = JSON.parse(raw);
  if (ver < LIBRARY_VER) {
    library = mergeLibraryEntries(library);
    localStorage.setItem('at_library', JSON.stringify(library));
    localStorage.setItem('at_library_ver', String(LIBRARY_VER));
  }
  return library;
}
function saveLibrary(d) {
  localStorage.setItem('at_library', JSON.stringify(d));
  localStorage.setItem('at_library_ver', String(LIBRARY_VER));
}
function getLibraryEntry(userId, workId) {
  return getLibrary().find(e => e.userId === Number(userId) && e.workId === Number(workId)) || null;
}
function saveLibraryEntry(entry) {
  const library = getLibrary();
  const existingIndex = library.findIndex(e => e.userId === Number(entry.userId) && e.workId === Number(entry.workId));
  if (existingIndex >= 0) {
    library[existingIndex] = { ...library[existingIndex], ...entry, updatedAt: new Date().toISOString().split('T')[0] };
  } else {
    library.push({ ...entry, id: Date.now(), createdAt: new Date().toISOString().split('T')[0] });
  }
  saveLibrary(library);
  return getLibraryEntry(entry.userId, entry.workId);
}

function getFriends() {
  const raw = localStorage.getItem('at_friends');
  const ver = Number(localStorage.getItem('at_friends_ver') || 0);
  if (!raw) {
    const fresh = JSON.parse(JSON.stringify(SEED_FRIENDS));
    localStorage.setItem('at_friends', JSON.stringify(fresh));
    localStorage.setItem('at_friends_ver', String(FRIENDS_VER));
    return fresh;
  }
  let friends = JSON.parse(raw);
  if (ver < FRIENDS_VER) {
    friends = mergeFriends(friends);
    localStorage.setItem('at_friends', JSON.stringify(friends));
    localStorage.setItem('at_friends_ver', String(FRIENDS_VER));
  }
  return friends;
}
function saveFriends(d) {
  localStorage.setItem('at_friends', JSON.stringify(d));
  localStorage.setItem('at_friends_ver', String(FRIENDS_VER));
}
function getFriendsByUser(userId) {
  return getFriends().filter(friend => friend.userId === Number(userId));
}
function isFriend(userId, friendId) {
  return getFriends().some(friend => friend.userId === Number(userId) && friend.friendId === Number(friendId));
}
function addFriend(userId, friendId) {
  if (Number(userId) === Number(friendId) || isFriend(userId, friendId)) return;
  const friends = getFriends();
  friends.push({
    id: Date.now(),
    userId: Number(userId),
    friendId: Number(friendId),
    createdAt: new Date().toISOString().split('T')[0]
  });
  saveFriends(friends);
}
function removeFriend(userId, friendId) {
  saveFriends(getFriends().filter(friend => !(friend.userId === Number(userId) && friend.friendId === Number(friendId))));
}

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

function updateUser(userId, updates) {
  const users = getUsers();
  const index = users.findIndex(user => user.id === Number(userId));
  if (index < 0) return null;
  users[index] = { ...users[index], ...updates };
  saveUsers(users);
  const session = getCurrentUser();
  if (session && session.id === Number(userId)) {
    sessionStorage.setItem('at_session', JSON.stringify({
      ...session,
      username:  users[index].username,
      role:      users[index].role,
      avatarUrl: users[index].avatarUrl || null
    }));
  }
  return users[index];
}

function requireAuth() {
  const u = getCurrentUser();
  if (!u) { window.location.href = 'login.html?from=' + encodeURIComponent(window.location.href); return null; }
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

function userProfileLink(user, fallback = 'User', className = 'user-link') {
  if (!user) return `<span>${escHtml(fallback)}</span>`;
  return `<a href="user-profile.html?user_id=${user.id}" class="${escHtml(className)}">${escHtml(user.username)}</a>`;
}

function avatarHtml(user, className = 'profile-avatar') {
  if (!user) return `<div class="${escHtml(className)}">?</div>`;
  if (user.avatarUrl) {
    return `<img class="${escHtml(className)} avatar-img" src="${escHtml(user.avatarUrl)}" alt="${escHtml(user.username)} avatar" onerror="this.style.display='none'">`;
  }
  return `<div class="${escHtml(className)}">${escHtml(user.username.charAt(0).toUpperCase())}</div>`;
}

function compressImage(dataUrl, callback) {
  const img = new Image();
  img.onload = () => {
    const MAX = 300;
    const scale = Math.min(1, MAX / img.width, MAX / img.height);
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(img.width * scale);
    canvas.height = Math.round(img.height * scale);
    canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
    callback(canvas.toDataURL('image/jpeg', 0.82));
  };
  img.src = dataUrl;
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
      <a href="admin-users.html"${cls('admin')}>Admin ▾</a>
      <ul class="dropdown-menu">
        <li><a href="admin-users.html">Manage Users</a></li>
      </ul>
    </li>` : '';

  const miniAvatar = u
    ? (u.avatarUrl
        ? `<img class="mini-avatar avatar-img" src="${escHtml(u.avatarUrl)}" alt="">`
        : `<span class="mini-avatar">${escHtml(u.username.charAt(0).toUpperCase())}</span>`)
    : '';

  const authLinks = u ? `
    <li><a href="dashboard.html"${cls('dashboard')}>Dashboard</a></li>
    <li><a href="add-work.html"${cls('add-work')}>Add Work</a></li>
    ${adminLinks}
    <li class="has-dropdown">
      <a href="profile.html"${cls('profile')} style="display:flex;align-items:center;gap:8px">${miniAvatar}${escHtml(u.username)}</a>
      <ul class="dropdown-menu">
        <li><a href="profile.html">My Profile</a></li>
        <li><a href="#" onclick="doLogout();return false;" class="nav-logout">Logout</a></li>
      </ul>
    </li>
  ` : `
    <li><a href="login.html"${cls('login')}>Login</a></li>
    <li><a href="register.html"${cls('register')}>Register</a></li>
  `;

  document.querySelector('header').innerHTML = `
    <nav class="navbar">
      <a href="${u ? 'dashboard.html' : 'index.html'}" class="logo">
        <img src="../assets/images/anime logo.png" alt="" aria-hidden="true">
        <span>AniTrack</span>
      </a>
      <ul class="nav-links">
        ${u ? '' : `<li><a href="index.html"${cls('home')}>Home</a></li>`}
        <li><a href="works.html"${cls('works')}>Works</a></li>
        ${authLinks}
      </ul>
    </nav>`;
}
