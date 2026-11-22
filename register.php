<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect(BASE_URL . 'index.php');
}

$data  = ['username' => '', 'nama_ikan' => '', 'jenis_ikan' => ''];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['username']   = trim($_POST['username'] ?? '');
    $data['nama_ikan']  = trim($_POST['nama_ikan'] ?? '');
    $data['jenis_ikan'] = trim($_POST['jenis_ikan'] ?? '');
    $password           = $_POST['password'] ?? '';
    $password2          = $_POST['password2'] ?? '';

    if ($data['username'] === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $data['username'])) {
        $error = 'Username 3-20 karakter, hanya huruf, angka, dan garis bawah.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $password2) {
        $error = 'Konfirmasi password tidak sama.';
    } elseif (!$db_connected) {
        $error = 'MySQL belum aktif, registrasi hanya bisa dilakukan setelah database menyala.';
    } elseif (find_user($data['username'])) {
        $error = 'Username sudah dipakai, silakan pilih yang lain.';
    } else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $id_baru = db_insert(
            "INSERT INTO users (username, password, nama_ikan, jenis_ikan)
             VALUES (?, ?, ?, ?)",
            [
                $data['username'],
                $hash,
                $data['nama_ikan'] === '' ? 'Ikan Baru' : $data['nama_ikan'],
                $data['jenis_ikan'] === '' ? 'Spesies belum diisi' : $data['jenis_ikan'],
            ]
        );

        if ($id_baru > 0) {
            $user = [
                'id'         => $id_baru,
                'username'   => $data['username'],
                'nama_ikan'  => $data['nama_ikan'] ?: 'Ikan Baru',
                'jenis_ikan' => $data['jenis_ikan'] ?: 'Spesies belum diisi',
            ];

            login_user($user);
            set_flash('ok', 'Akun berhasil dibuat. Lengkapi data ikan di menu Data Ikan.');
            redirect(BASE_URL . 'index.php');
        }

        $error = 'Akun gagal dibuat. Coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar &middot; <?= APP_NAME ?></title>
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

    <span class="auth-step">STEP 00 // PENDAFTARAN USER</span>
    <h1>Buat Akun</h1>
    <p class="auth-sub">Isi data di bawah untuk membuat akun baru, lalu mulai
       catat perawatan ikan Anda.</p>

    <?php if ($error !== '') { ?>
      <div class="flash flash-danger">
        <span class="flash-mark">!</span> <?= e($error) ?>
      </div>
    <?php } ?>

    <form method="post" action="<?= BASE_URL ?>register.php"
          class="form-cyber" data-warn-unsaved>
      <div class="form-row">
        <label class="form-label" for="username">Username</label>
        <input class="form-input" type="text" id="username" name="username"
               value="<?= e($data['username']) ?>" placeholder="misal: fadilah"
               minlength="3" maxlength="20" required autofocus>
        <div class="form-hint">3-20 karakter, huruf, angka, atau garis bawah.</div>
      </div>

      <div class="form-row">
        <label class="form-label" for="nama_ikan">Nama ikan</label>
        <input class="form-input" type="text" id="nama_ikan" name="nama_ikan"
               value="<?= e($data['nama_ikan']) ?>" placeholder="misal: Alpha Striker"
               maxlength="60">
      </div>

      <div class="form-row">
        <label class="form-label" for="jenis_ikan">Jenis ikan</label>
        <input class="form-input" type="text" id="jenis_ikan" name="jenis_ikan"
               value="<?= e($data['jenis_ikan']) ?>" placeholder="misal: Channa Marulioides"
               maxlength="60">
      </div>

      <div class="form-row">
        <label class="form-label" for="password">Password</label>
        <input class="form-input" type="password" id="password" name="password"
               placeholder="Minimal 6 karakter" minlength="6" required>
      </div>

      <div class="form-row">
        <label class="form-label" for="password2">Ulangi password</label>
        <input class="form-input" type="password" id="password2" name="password2"
               placeholder="Ketik ulang password" minlength="6" required>
      </div>

      <button class="btn-cyber auth-submit" type="submit">DAFTAR AKUN &rarr;</button>
    </form>

    <div class="auth-links">
      Sudah punya akun? <a href="<?= BASE_URL ?>login.php">Masuk di sini</a>
    </div>
  </div>

  <script src="<?= BASE_URL ?>assets/js/pages.js"></script>
</body>
</html>
