<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();
$id   = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'Data habit tidak valid.');
    redirect(BASE_URL . 'habit/index.php');
}

$daftar_kategori = ['PAKAN', 'KUALITAS AIR', 'SANITASI', 'KESEHATAN', 'SUHU & LAMPU'];
$pilihan_emblem  = ['🐟', '🐠', '🐡', '🦈', '💧', '🌡️', '🧽', '💊', '⏱️'];
$label_emblem = [
    '🐟' => 'Ikan', '🐠' => 'Tropical', '🐡' => 'Buntal', '🦈' => 'Hiu',
    '💧' => 'Air', '🌡️' => 'Suhu', '🧽' => 'Bersih', '💊' => 'Obat',
    '⏱️' => 'Jadwal',
];

$errors = [];

$habit = $db_connected
    ? db_row(
        "SELECT id, nama_habit, kategori, waktu, emblem, target_harian
         FROM habits WHERE id = ? AND user_id = ? LIMIT 1",
        'ii',
        [$id, (int)$user['id']]
    )
    : null;

if (!$habit && !$db_connected) {
    foreach (demo_habits() as $h) {
        if ((int)$h['id'] === $id) {
            $habit = $h;
            break;
        }
    }
}

if (!$habit) {
    set_flash('danger', 'Habit tidak ditemukan atau bukan milik akun ini.');
    redirect(BASE_URL . 'habit/index.php');
}

$data = $habit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['nama_habit', 'kategori', 'waktu', 'emblem', 'target_harian'] as $kolom) {
        $data[$kolom] = trim((string)($_POST[$kolom] ?? ''));
    }

    $target = (int)$data['target_harian'];

    if ($data['nama_habit'] === '') {
        $errors[] = 'Nama habit wajib diisi.';
    }

    if (!in_array($data['kategori'], $daftar_kategori, true)) {
        $errors[] = 'Kategori tidak dikenali.';
    }

    if ($target < 1 || $target > 10) {
        $errors[] = 'Target harian harus antara 1 sampai 10.';
    }

    if (!in_array($data['emblem'], $pilihan_emblem, true)) {
        $data['emblem'] = '🐟';
    }

    if (empty($errors) && !$db_connected) {
        $errors[] = 'Database belum aktif, perubahan tidak bisa disimpan.';
    }

    if (empty($errors)) {
        $berhasil = db_exec(
            "UPDATE habits
             SET nama_habit = ?, kategori = ?, waktu = ?, emblem = ?, target_harian = ?
             WHERE id = ? AND user_id = ?",
            [
                $data['nama_habit'],
                $data['kategori'],
                $data['waktu'] === '' ? 'Rutin Harian' : $data['waktu'],
                $data['emblem'],
                $target,
                $id,
                (int)$user['id'],
            ]
        );

        if ($berhasil) {
            set_flash('ok', 'Habit "' . $data['nama_habit'] . '" berhasil diperbarui.');
            redirect(BASE_URL . 'habit/index.php');
        }

        $errors[] = pesan_gagal('habit');
    }
}

$page_title   = 'Edit Habit';
$page_eyebrow = 'PROTOKOL PERAWATAN';
$page_desc    = 'Perubahan berlaku untuk pencatatan hari ini dan seterusnya.';
$active       = 'habit';

include __DIR__ . '/../includes/header.php';
?>

<div class="split">
  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// EDIT_HABIT</h2>
      <span class="panel-sub">DATA #<?= $id ?></span>
    </div>

    <div class="panel-body">
      <?php if (!empty($errors)) { ?>
        <div class="flash flash-danger">
          <span class="flash-mark">✕</span>
          <ul style="margin:0;padding-left:16px;">
            <?php foreach ($errors as $err) { ?>
              <li><?= e($err) ?></li>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>

      <form method="post" action="<?= BASE_URL ?>habit/edit.php?id=<?= $id ?>"
            class="form-cyber" data-warn-unsaved>
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="form-row">
          <label class="form-label" for="nama_habit">Nama habit</label>
          <input class="form-input" type="text" id="nama_habit" name="nama_habit"
                 value="<?= e($data['nama_habit']) ?>" maxlength="100" required>
        </div>

        <div class="form-grid">
          <div class="form-row" style="margin:0;">
            <label class="form-label" for="kategori">Kategori</label>
            <select class="form-select" id="kategori" name="kategori">
              <?php foreach ($daftar_kategori as $k) { ?>
                <option value="<?= e($k) ?>" <?= $data['kategori'] === $k ? 'selected' : '' ?>>
                  <?= e($k) ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="form-row" style="margin:0;">
            <label class="form-label" for="waktu">Jadwal</label>
            <input class="form-input" type="text" id="waktu" name="waktu"
                   value="<?= e($data['waktu']) ?>" maxlength="40">
          </div>

          <div class="form-row" style="margin:0;">
            <label class="form-label" for="target_harian">Target harian</label>
            <input class="form-input" type="number" id="target_harian" name="target_harian"
                   value="<?= (int)$data['target_harian'] ?>" min="1" max="10">
          </div>

          <div class="form-row" style="margin:0;">
            <label class="form-label" for="emblem">Emblem</label>
            <select class="form-select" id="emblem" name="emblem">
              <?php foreach ($pilihan_emblem as $em) { ?>
                <option value="<?= $em ?>" <?= $data['emblem'] === $em ? 'selected' : '' ?>>
                  <?= $em ?> <?= $label_emblem[$em] ?? '' ?>
                </option>
              <?php } ?>
            </select>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn-cyber" type="submit">SIMPAN PERUBAHAN</button>
          <a class="btn-cyber ghost" href="<?= BASE_URL ?>habit/index.php">BATAL</a>
        </div>
      </form>

      <form method="post" action="<?= BASE_URL ?>habit/hapus.php"
            style="margin-top:18px;padding-top:16px;border-top:1px dashed var(--border-color);">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button class="btn-cyber danger" type="submit"
                data-confirm="Hapus habit <?= e($data['nama_habit']) ?> beserta seluruh riwayat centangnya?">
          HAPUS HABIT INI
        </button>
      </form>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// DATA_TERSIMPAN</h2>
      <span class="panel-sub">SEBELUM DIUBAH</span>
    </div>
    <div class="panel-body">
      <div class="kv-list">
        <div class="kv-row"><span class="kv-key">Nama</span><span class="kv-val"><?= e($habit['nama_habit']) ?></span></div>
        <div class="kv-row"><span class="kv-key">Kategori</span><span class="kv-val"><?= e($habit['kategori']) ?></span></div>
        <div class="kv-row"><span class="kv-key">Jadwal</span><span class="kv-val"><?= e($habit['waktu']) ?></span></div>
        <div class="kv-row"><span class="kv-key">Target</span><span class="kv-val"><?= (int)$habit['target_harian'] ?>x / hari</span></div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
