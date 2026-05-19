<?php
// このファイルを db.php にコピーして、自分のDB情報を入力してください。
// db.php は .gitignore で除外されています — 絶対にGitにコミットしないこと。

// ── XAMPP (ローカル開発) ──
// $host     = 'localhost';
// $dbname   = 'anitrack';
// $username = 'root';
// $password = '';

// ── InfinityFree ──
// $host     = 'sqlXXX.epizy.com';          // コントロールパネルのMySQL設定で確認
// $dbname   = 'epiz_XXXXXXXX_anitrack';    // DB名 (epiz_アカウントID_anitrack)
// $username = 'epiz_XXXXXXXX';             // MySQLユーザー名
// $password = 'your_db_password';

$host     = 'YOUR_DB_HOST';
$dbname   = 'YOUR_DB_NAME';
$username = 'YOUR_DB_USER';
$password = 'YOUR_DB_PASSWORD';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}
