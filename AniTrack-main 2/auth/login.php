<?php
session_start();
require_once __DIR__ . '/../config/app.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/home.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/db.php';

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Email and password are required.';
    } else {
        $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: ' . BASE_URL . '/pages/home.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle  = 'Login - AniTrack';
$activePage = 'login';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section">
  <div class="page-header">
    <h2>Login</h2>
    <p>Welcome back to AniTrack</p>
  </div>

  <div class="form-box">
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="email">Email</label>
      <input type="email" id="email" name="email"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
             placeholder="your@email.com" required autocomplete="email">

      <label for="password">Password</label>
      <input type="password" id="password" name="password"
             placeholder="Enter your password" required autocomplete="current-password">

      <button type="submit" class="btn" style="width:100%">Login</button>
    </form>

    <p style="text-align:center;margin-top:18px;font-size:14px;color:var(--muted)">
      Don't have an account?
      <a href="<?= BASE_URL ?>/auth/register.php" style="color:var(--accent)">Register</a>
    </p>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
