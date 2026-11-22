<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user  = current_user();
$error = '';

if (($_POST['aksi'] ?? '') === 'akun') {
    $nama_ikan  = trim($_POST['nama_ikan'] ?? '');
    $jenis_ikan = trim($_POST['jenis_ikan'] ?? '');

    if ($nama_ikan === '' || $jenis_ikan === '') {
        $error = 'Nama ikan dan jenis ikan wajib diisi.';
    } elseif (mb_strlen($nama_ikan) > 60 || mb_strlen($jenis_ikan) > 60) {
        $error = 'Nama ikan dan jenis ikan maksimal 60 karakter.';
    } elseif (!$db_connected) {
        $_SESSION['nama_ikan']  = $nama_ikan;
        $_SESSION['jenis_ikan'] = $jenis_ikan;

        set_flash('warn', 'Database belum aktif, perubahan hanya berlaku selama sesi ini.');
        redirect(BASE_URL . 'profil.php');
    } else {
        $berhasil = db_exec(
            "UPDATE users SET nama_ikan = ?, jenis_ikan = ? WHERE id = ?",
            [$nama_ikan, $jenis_ikan, (int)$user['id']]
        );

        if ($berhasil) {
            $_SESSION['nama_ikan']  = $nama_ikan;
            $_SESSION['jenis_ikan'] = $jenis_ikan;

            set_flash('ok', 'Data ikan pada akun berhasil diperbarui.');
            redirect(BASE_URL . 'profil.php');
        }

        $error = 'Data akun gagal disimpan. Coba lagi.';
    }
}

if (($_POST['aksi'] ?? '') === 'password') {
    $lama   = $_POST['password_lama'] ?? '';
    $baru   = $_POST['password_baru'] ?? '';
    $ulangi = $_POST['password_ulangi'] ?? '';

    $akun = find_user($user['username']);

    if (!$akun || !check_password($lama, $akun['password'])) {
        $error = 'Password lama tidak sesuai.';
    } elseif (strlen($baru) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } elseif ($baru !== $ulangi) {
        $error = 'Konfirmasi password baru tidak sama.';
    } elseif (!$db_connected) {
        $error = 'Database belum aktif, password tidak bisa diganti.';
    } else {
        $hash = password_hash($baru, PASSWORD_DEFAULT);

        if (db_exec("UPDATE users SET password = ? WHERE id = ?", [$hash, (int)$user['id']])) {
            set_flash('ok', 'Password berhasil diganti. Gunakan password baru saat login berikutnya.');
            redirect(BASE_URL . 'profil.php');
        }

        $error = 'Password gagal diganti. Coba lagi.';
    }
}

if (($_POST['aksi'] ?? '') === 'tema') {
    $tema = $_POST['tema'] ?? 'abyss-dark';

    if (!in_array($tema, ['abyss-dark', 'mint-cyan'], true)) {
        $tema = 'abyss-dark';
    }

    setcookie('ikan_theme', $tema, time() + 60 * 60 * 24 * 90, '/');
    set_flash('ok', 'Tema tampilan disimpan.');
    redirect(BASE_URL . 'profil.php');
}

$jumlah_habit = count(ambil_habits($user['id']));
$jumlah_ikan  = count(ambil_fishes($user['id']));
$jumlah_pakan = count(ambil_feedings($user['id'], 500));

$page_title   = 'Profil & Pengaturan';
$page_eyebrow = 'AKUN';
$page_desc    = 'Kelola data ikan utama, tampilan aplikasi, dan password akun.';
$active       = 'profil';

include __DIR__ . '/includes/header.php';
?>

<?php if ($error !== '') { ?>
  <div class="flash flash-danger"><span class="flash-mark">!</span> <?= e($error) ?></div>
<?php } ?>

<div class="stat-grid">
  <div class="stat-box">
    <div class="stat-label">Habit Aktif</div>
    <div class="stat-number"><?= $jumlah_habit ?></div>
    <div class="stat-note dim">PROTOKOL</div>
  </div>
  <div class="stat-box">
    <div class="stat-label">Spesimen</div>
    <div class="stat-number"><?= $jumlah_ikan ?></div>
    <div class="stat-note dim">TERDAFTAR</div>
  </div>
  <div class="stat-box">
    <div class="stat-label">Catatan Pakan</div>
    <div class="stat-number"><?= $jumlah_pakan ?></div>
    <div class="stat-note dim">TERSIMPAN</div>
  </div>
</div>

<div class="split equal">
  <div>
    <div class="panel">
      <div class="panel-head">
        <h2 class="panel-title">// PROFIL_AKUN</h2>
        <span class="panel-sub"><?= e($user['username']) ?></span>
      </div>

      <div class="panel-body">
        <div class="kv-list">
          <div class="kv-row"><span class="kv-key">Username</span><span class="kv-val mono"><?= e($user['username']) ?></span></div>
          <div class="kv-row"><span class="kv-key">Nama ikan</span><span class="kv-val"><?= e($user['nama_ikan']) ?></span></div>
          <div class="kv-row"><span class="kv-key">Jenis ikan</span><span class="kv-val"><?= e($user['jenis_ikan']) ?></span></div>
          <div class="kv-row"><span class="kv-key">Tema tampilan</span><span class="kv-val"><?= $theme_mode === 'mint-cyan' ? 'Mint Cyan' : 'Abyss Dark' ?></span></div>
        </div>

        <h3 class="form-label" style="margin-top:22px;">Ubah tampilan</h3>
        <form method="post" action="<?= BASE_URL ?>profil.php" style="display:flex;gap:8px;flex-wrap:wrap;">
          <input type="hidden" name="aksi" value="tema">
          <select class="form-select" name="tema" style="max-width:200px;">
            <option value="abyss-dark" <?= $theme_mode === 'abyss-dark' ? 'selected' : '' ?>>Abyss Dark (bawaan)</option>
            <option value="mint-cyan"  <?= $theme_mode === 'mint-cyan' ? 'selected' : '' ?>>Mint Cyan</option>
          </select>
          <button class="btn-cyber ghost" type="submit">SIMPAN TAMPILAN</button>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <h2 class="panel-title">// DATA_IKAN_UTAMA</h2>
      </div>

      <div class="panel-body">
        <form method="post" action="<?= BASE_URL ?>profil.php" class="form-cyber">
          <input type="hidden" name="aksi" value="akun">

          <div class="form-row">
            <label class="form-label" for="nama_ikan">Nama ikan utama</label>
            <input class="form-input" type="text" id="nama_ikan" name="nama_ikan"
                   value="<?= e($user['nama_ikan']) ?>" maxlength="60" required>
          </div>

          <div class="form-row">
            <label class="form-label" for="jenis_ikan">Jenis ikan</label>
            <input class="form-input" type="text" id="jenis_ikan" name="jenis_ikan"
                   value="<?= e($user['jenis_ikan']) ?>" maxlength="60" required>
          </div>

          <div class="form-actions">
            <button class="btn-cyber" type="submit">SIMPAN DATA</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div>
    <div class="panel">
      <div class="panel-head">
        <h2 class="panel-title">// GANTI_PASSWORD</h2>
      </div>

      <div class="panel-body">
        <form method="post" action="<?= BASE_URL ?>profil.php" class="form-cyber">
          <input type="hidden" name="aksi" value="password">

          <div class="form-row">
            <label class="form-label" for="password_lama">Password lama</label>
            <input class="form-input" type="password" id="password_lama" name="password_lama"
                   autocomplete="current-password" required>
          </div>

          <div class="form-row">
            <label class="form-label" for="password_baru">Password baru</label>
            <input class="form-input" type="password" id="password_baru" name="password_baru"
                   minlength="6" autocomplete="new-password" required>
            <div class="form-hint">Minimal 6 karakter.</div>
          </div>

          <div class="form-row">
            <label class="form-label" for="password_ulangi">Ulangi password baru</label>
            <input class="form-input" type="password" id="password_ulangi" name="password_ulangi"
                   minlength="6" autocomplete="new-password" required>
          </div>

          <div class="form-actions">
            <button class="btn-cyber ghost" type="submit">GANTI PASSWORD</button>
          </div>
        </form>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <h2 class="panel-title">// CATATAN_KEAMANAN</h2>
      </div>
      <div class="panel-body">
        <p class="page-desc" style="margin:0;">
          Password disimpan dalam bentuk terenkripsi satu arah, sehingga isi
          password asli tidak bisa dilihat kembali. Bila lupa password, hubungi
          pengelola aplikasi untuk mengatur ulang.
        </p>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
