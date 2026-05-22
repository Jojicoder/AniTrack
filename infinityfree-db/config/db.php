<?php
// ローカル(XAMPP)用デフォルト設定
// InfinityFree にデプロイする際はここを書き換えてください
$host     = 'sql206.infinityfree.com';
$dbname   = 'if0_41924395_anitrack';
$username = 'if0_41924395';
$password = 'O9tU9qLWBr5o42';

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
