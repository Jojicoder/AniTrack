<?php
if (!defined('BASE_URL')) require_once __DIR__ . '/../config/app.php';

function avatarImg($avatar, $username, $class = 'profile-avatar') {
    $cls = htmlspecialchars($class, ENT_QUOTES);
    $sizes = [
        'profile-avatar' => 'width:72px;height:72px;border-radius:50%;object-fit:cover;display:block;flex-shrink:0;',
        'mini-avatar'    => 'width:28px;height:28px;border-radius:50%;object-fit:cover;display:inline-block;vertical-align:middle;flex-shrink:0;',
        'social-avatar'  => 'width:44px;height:44px;border-radius:50%;object-fit:cover;display:block;flex-shrink:0;',
    ];
    $style = $sizes[$class] ?? 'width:72px;height:72px;border-radius:50%;object-fit:cover;display:block;';
    if ($avatar) {
        $url = BASE_URL . '/uploads/avatars/' . rawurlencode($avatar);
        return '<img class="' . $cls . '" src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($username) . ' avatar" style="' . $style . '">';
    }
    $initial = htmlspecialchars(strtoupper(substr($username, 0, 1)));
    return '<div class="' . $cls . '">' . $initial . '</div>';
}

function starsHtml($rating) {
    if (!$rating) return '<span style="color:var(--muted);font-size:13px">Not rated yet</span>';
    $r = (int)$rating;
    return '<span class="stars">' . str_repeat('★', $r) . str_repeat('☆', 10 - $r) . '</span> '
         . '<span style="color:var(--muted);font-size:13px">' . $r . ' / 10</span>';
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken(), ENT_QUOTES) . '">';
}

function genreColors() {
    return [
        'Action'        => ['#7b3ff2cc', '#7b3ff244'],
        'Adventure'     => ['#1d4ed8cc', '#1d4ed844'],
        'Dark Fantasy'  => ['#6b21a8cc', '#6b21a844'],
        'Historical'    => ['#92400ecc', '#92400e44'],
        'Slice of Life' => ['#0891b2cc', '#0891b244'],
        'Romance'       => ['#db2777cc', '#db277744'],
        'Comedy'        => ['#16a34acc', '#16a34a44'],
        'Fantasy'       => ['#d97706cc', '#d9770644'],
        'Sci-Fi'        => ['#0369a1cc', '#0369a144'],
        'Sports'        => ['#15803dcc', '#15803d44'],
        'Drama'         => ['#475569cc', '#47556944'],
        'Horror'        => ['#dc2626cc', '#dc262644'],
        'Mystery'       => ['#7c3aedcc', '#7c3aed44'],
    ];
}
