<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();

$data = [
    'nama_habit'    => '',
    'kategori'      => 'PAKAN',
    'waktu'         => '',
    'emblem'        => '🐟',
    'target_harian' => 1,
];
$errors = [];

$daftar_kategori = ['PAKAN', 'KUALITAS AIR', 'SANITASI', 'KESEHATAN', 'SUHU & LAMPU'];
$pilihan_emblem  = ['🐟', '🐠', '🐡', '🦈', '💧', '🌡️', '🧽', '💊', '⏱️'];
$label_emblem = [
    '🐟' => 'Ikan', '🐠' => 'Tropical', '🐡' => 'Buntal', '🦈' => 'Hiu',
    '💧' => 'Air', '🌡️' => 'Suhu', '🧽' => 'Bersih', '💊' => 'Obat',
    '⏱️' => 'Jadwal',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($data) as $kunci) {
        $data[$kunci] = trim((string)($_POST[$kunci] ?? $data[$kunci]));
    }

    $target = (int)$data['target_harian'];

    if ($data['nama_habit'] === '') {
        $errors[] = 'Nama habit wajib diisi.';
    } elseif (mb_strlen($data['nama_habit']) > 100) {
        $errors[] = 'Nama habit maksimal 100 karakter.';
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
        $errors[] = 'Database belum aktif. Nyalakan MySQL di XAMPP untuk menyimpan habit.';
    }

    if (empty($errors)) {
        $id_baru = db_insert(
            "INSERT INTO habits (user_id, nama_habit, kategori, waktu, emblem, target_harian)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                (int)$user['id'],
                $data['nama_habit'],
                $data['kategori'],
                $data['waktu'] === '' ? 'Rutin Harian' : $data['waktu'],
                $data['emblem'],
                $target,
            ]
        );

        if ($id_baru > 0) {
            set_flash('ok', 'Habit "' . $data['nama_habit'] . '" berhasil ditambahkan.');
            redirect(BASE_URL . 'habit/index.php');
        }

        $errors[] = pesan_gagal('habit');
    }
}

$page_title   = 'Tambah Habit';
$page_eyebrow = 'PROTOKOL PERAWATAN';
$page_desc    = 'Protokol baru langsung muncul di dashboard setelah disimpan.';
$active       = 'habit';

include __DIR__ . '/../includes/header.php';
?>

<div class="split">
  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// FORM_HABIT_BARU</h2>
      <span class="panel-sub">BARU</span>
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

      <form method="post" action="<?= BASE_URL ?>habit/tambah.php"
            class="form-cyber" data-warn-unsaved>
        <div class="form-row">
          <label class="form-label" for="nama_habit">Nama habit / protokol</label>
          <input class="form-input" type="text" id="nama_habit" name="nama_habit"
                 value="<?= e($data['nama_habit']) ?>" maxlength="100"
                 placeholder="misal: Beri pakan pelet pagi" required autofocus>
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
            <label class="form-label" for="waktu">Jadwal / waktu</label>
            <input class="form-input" type="text" id="waktu" name="waktu"
                   value="<?= e($data['waktu']) ?>" maxlength="40"
                   placeholder="misal: 07:30 WIB">
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
          <button class="btn-cyber" type="submit">SIMPAN HABIT</button>
          <a class="btn-cyber ghost" href="<?= BASE_URL ?>habit/index.php">BATAL</a>
        </div>
      </form>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// PANDUAN_KATEGORI</h2>
    </div>
    <div class="panel-body">
      <div class="kv-list">
        <div class="kv-row"><span class="kv-key">PAKAN</span><span class="kv-val">Pelet, cacing, artemia</span></div>
        <div class="kv-row"><span class="kv-key">KUALITAS AIR</span><span class="kv-val">Water change, anti-klorin</span></div>
        <div class="kv-row"><span class="kv-key">SANITASI</span><span class="kv-val">Filter, siphon, kaca akuarium</span></div>
        <div class="kv-row"><span class="kv-key">KESEHATAN</span><span class="kv-val">Inspeksi sirip, sisik, nafsu makan</span></div>
        <div class="kv-row"><span class="kv-key">SUHU & LAMPU</span><span class="kv-val">Heater, aerator, pencahayaan</span></div>
      </div>

      <p class="form-hint" style="margin-top:16px;">
        Target harian dipakai bila satu protokol perlu dilakukan beberapa kali
        sehari, misalnya pemberian pakan pagi dan sore.
      </p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
