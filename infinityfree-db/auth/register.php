<?php
session_start();
require_once __DIR__ . '/../config/app.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/home.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/db.php';

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']         ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!$username || !$email || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($username) < 3 || strlen($username) > 30) {
        $error = 'Username must be 3–30 characters.';
    } elseif (!preg_match('/^[A-Za-z0-9_]+$/', $username)) {
        $error = 'Username may only contain letters, numbers, and underscores.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            $error = 'Email or username is already in use.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$username, $email, $hash]);
            $success = 'Account created! You can now log in.';
        }
    }
}

$pageTitle  = 'Register - AniTrack';
$activePage = 'register';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="section" style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:calc(100vh - 120px)">
  <div class="page-header">
    <h2>Create Account</h2>
    <p>Join AniTrack and start building your library</p>
  </div>

  <div class="form-box">
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success">
        <?= htmlspecialchars($success) ?><br>
        <a href="<?= BASE_URL ?>/auth/login.php" style="color:inherit;text-decoration:underline">Go to Login &rarr;</a>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="username">Username</label>
      <input type="text" id="username" name="username"
             value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
             placeholder="Choose a username"
             required minlength="3" maxlength="30"
             pattern="[A-Za-z0-9_]+" title="Letters, numbers and underscores only">

      <label for="email">Email</label>
      <input type="email" id="email" name="email"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
             placeholder="your@email.com" required>

      <label for="password">Password</label>
      <input type="password" id="password" name="password"
             placeholder="At least 6 characters" required minlength="6">

      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password"
             placeholder="Enter password again" required>

      <button type="submit" class="btn" style="width:100%">Create Account</button>
    </form>

    <p style="text-align:center;margin-top:18px;font-size:14px;color:var(--muted)">
      Already have an account?
      <a href="<?= BASE_URL ?>/auth/login.php" style="color:var(--accent)">Log in</a>
    </p>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
