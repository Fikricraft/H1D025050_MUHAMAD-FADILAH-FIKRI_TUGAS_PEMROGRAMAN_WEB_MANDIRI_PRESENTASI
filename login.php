<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect(BASE_URL . 'index.php');
}

$username = '';
$error    = '';

$next = $_GET['next'] ?? BASE_URL . 'index.php';
if (str_contains($next, '://') || !str_starts_with($next, '/')) {
    $next = BASE_URL . 'index.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $ingat    = isset($_POST['ingat']);

    $error = attempt_login($username, $password);

    if ($error === null) {
        if ($ingat) {
            set_remember_cookie($_SESSION['username']);
        }
        set_flash('ok', 'Login berhasil. Selamat datang, ' . $_SESSION['username'] . '.');
        redirect($next);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login &middot; <?= APP_NAME ?></title>
  <link rel="icon" href="<?= BASE_URL ?>assets/img/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@400;600;700&family=JetBrains+Mono:wght@400;500;700&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/pages.css">
</head>
<body class="auth-page">

  <div class="grid-bg"></div>
  <div class="scanlines"></div>

  <div class="auth-card">
    <a class="auth-brand" href="<?= BASE_URL ?>index.php">
      IKAN<span class="accent">0//.</span>
    </a>
    <div class="auth-tagline">Fish Habit Tracking System</div>

    <span class="auth-step">STEP 01 // AUTENTIKASI USER</span>
    <h1>Masuk Terminal</h1>
    <p class="auth-sub">Gunakan username dan password yang terdaftar untuk
       membuka dashboard pemantauan ikan.</p>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'logout') { ?>
      <div class="flash flash-ok">
        <span class="flash-mark">■</span> Anda sudah keluar dari sistem.
      </div>
    <?php } ?>

    <?php if ($error !== '') { ?>
      <div class="flash flash-danger">
        <span class="flash-mark">!</span> <?= e($error) ?>
      </div>
    <?php } ?>

    <?php if (!$db_connected) { ?>
      <div class="flash flash-warn">
        <span class="flash-mark">▲</span>
        MySQL mati. Login memakai <b>akun demo</b>.
      </div>
    <?php } ?>

    <form method="post" action="<?= BASE_URL ?>login.php?next=<?= urlencode($next) ?>"
          class="form-cyber" data-warn-unsaved>
      <div class="form-row">
        <label class="form-label" for="username">Username</label>
        <input class="form-input" type="text" id="username" name="username"
               value="<?= e($username) ?>" placeholder="admin"
               autocomplete="username" required autofocus>
      </div>

      <div class="form-row">
        <label class="form-label" for="password">Password</label>
        <input class="form-input" type="password" id="password" name="password"
               placeholder="••••••••" autocomplete="current-password" required>
      </div>

      <label class="auth-check">
        <input type="checkbox" name="ingat" value="1">
        Ingat saya selama 30 hari (cookie)
      </label>

      <button class="btn-cyber auth-submit" type="submit">
        MASUK &rarr;
      </button>
    </form>

    <div class="auth-links">
      Belum punya akun? <a href="<?= BASE_URL ?>register.php">Daftar di sini</a>
    </div>

    <div class="auth-hint">
      AKUN DEMO &mdash; <b>admin</b> / <b>password123</b><br>
      Database: <?= $db_connected ? 'terhubung' : 'tidak aktif (mode demo)' ?>
    </div>
  </div>

  <script src="<?= BASE_URL ?>assets/js/pages.js"></script>
</body>
</html>
