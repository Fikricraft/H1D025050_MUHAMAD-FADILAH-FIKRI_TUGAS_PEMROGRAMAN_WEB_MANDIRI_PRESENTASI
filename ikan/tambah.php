<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();

$data = [
    'nama'       => '',
    'jenis'      => '',
    'ukuran_cm'  => '',
    'umur_bulan' => '',
    'kondisi'    => 'SEHAT',
    'catatan'    => '',
    'emblem'     => '🐟',
];
$errors = [];

$pilihan_emblem = ['🐟', '🐠', '🐡', '🦈', '💧', '🌡️', '🧽', '💊', '⏱️'];
$label_emblem = [
    '🐟' => 'Ikan', '🐠' => 'Tropical', '🐡' => 'Buntal', '🦈' => 'Hiu',
    '💧' => 'Air', '🌡️' => 'Suhu', '🧽' => 'Bersih', '💊' => 'Obat',
    '⏱️' => 'Jadwal',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($data) as $kunci) {
        $data[$kunci] = trim((string)($_POST[$kunci] ?? $data[$kunci]));
    }

    $ukuran = (float)str_replace(',', '.', (string)$data['ukuran_cm']);
    $umur   = (int)$data['umur_bulan'];

    if ($data['nama'] === '') {
        $errors[] = 'Nama ikan wajib diisi.';
    } elseif (mb_strlen($data['nama']) > 60) {
        $errors[] = 'Nama ikan maksimal 60 karakter.';
    }

    if ($data['jenis'] === '') {
        $errors[] = 'Jenis ikan wajib diisi.';
    } elseif (mb_strlen($data['jenis']) > 60) {
        $errors[] = 'Jenis ikan maksimal 60 karakter.';
    }

    if ($ukuran <= 0 || $ukuran > 200) {
        $errors[] = 'Ukuran harus angka antara 0,1 sampai 200 cm.';
    }

    if ($umur < 0 || $umur > 600) {
        $errors[] = 'Umur harus antara 0 sampai 600 bulan.';
    }

    if (!in_array($data['kondisi'], ['SEHAT', 'PERHATIAN', 'SAKIT'], true)) {
        $errors[] = 'Kondisi tidak dikenali.';
    }

    if (!in_array($data['emblem'], $pilihan_emblem, true)) {
        $data['emblem'] = '🐟';
    }

    if (empty($errors) && !$db_connected) {
        $errors[] = 'Database belum aktif. Nyalakan MySQL di XAMPP untuk menyimpan data.';
    }

    if (empty($errors)) {
        $id_baru = db_insert(
            "INSERT INTO fishes (user_id, nama, jenis, ukuran_cm, umur_bulan, kondisi, catatan, emblem)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                (int)$user['id'],
                $data['nama'],
                $data['jenis'],
                $ukuran,
                $umur,
                $data['kondisi'],
                $data['catatan'] === '' ? null : $data['catatan'],
                $data['emblem'],
            ]
        );

        if ($id_baru > 0) {
            set_flash('ok', 'Spesimen "' . $data['nama'] . '" berhasil ditambahkan.');
            redirect(BASE_URL . 'ikan/index.php');
        }

        $errors[] = pesan_gagal('spesimen');
    }
}

$page_title   = 'Tambah Ikan';
$page_eyebrow = 'DATA IKAN';
$page_desc    = 'Lengkapi data spesimen yang ingin dipantau.';
$active       = 'ikan';

include __DIR__ . '/../includes/header.php';
?>

<div class="split">
  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// FORM_SPESIMEN_BARU</h2>
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

      <form method="post" action="<?= BASE_URL ?>ikan/tambah.php"
            class="form-cyber" data-warn-unsaved>

        <div class="form-grid">
          <div class="form-row" style="margin:0;">
            <label class="form-label" for="nama">Nama spesimen</label>
            <input class="form-input" type="text" id="nama" name="nama"
                   value="<?= e($data['nama']) ?>" maxlength="60"
                   placeholder="misal: Alpha Striker" required autofocus>
          </div>

          <div class="form-row" style="margin:0;">
            <label class="form-label" for="jenis">Jenis / spesies</label>
            <input class="form-input" type="text" id="jenis" name="jenis"
                   value="<?= e($data['jenis']) ?>" maxlength="60"
                   placeholder="misal: Channa Marulioides" required>
          </div>

          <div class="form-row" style="margin:0;">
            <label class="form-label" for="ukuran_cm">Ukuran (cm)</label>
            <input class="form-input" type="text" id="ukuran_cm" name="ukuran_cm"
                   value="<?= e($data['ukuran_cm']) ?>" placeholder="misal: 12.5" required>
            <div class="form-hint">Boleh memakai titik atau koma desimal.</div>
          </div>

          <div class="form-row" style="margin:0;">
            <label class="form-label" for="umur_bulan">Umur (bulan)</label>
            <input class="form-input" type="number" id="umur_bulan" name="umur_bulan"
                   value="<?= e($data['umur_bulan']) ?>" min="0" max="600"
                   placeholder="misal: 8" required>
          </div>
        </div>

        <div class="form-grid" style="margin-top:16px;">
          <div class="form-row" style="margin:0;">
            <label class="form-label" for="kondisi">Kondisi</label>
            <select class="form-select" id="kondisi" name="kondisi">
              <?php foreach (['SEHAT', 'PERHATIAN', 'SAKIT'] as $opsi) { ?>
                <option value="<?= $opsi ?>" <?= $data['kondisi'] === $opsi ? 'selected' : '' ?>>
                  <?= $opsi ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="form-row" style="margin:0;">
            <label class="form-label">Emblem</label>
            <select class="form-select" name="emblem">
              <?php foreach ($pilihan_emblem as $em) { ?>
                <option value="<?= $em ?>" <?= $data['emblem'] === $em ? 'selected' : '' ?>>
                  <?= $em ?> <?= $label_emblem[$em] ?? '' ?>
                </option>
              <?php } ?>
            </select>
          </div>
        </div>

        <div class="form-row" style="margin-top:16px;">
          <label class="form-label" for="catatan">Catatan perawatan</label>
          <textarea class="form-textarea" id="catatan" name="catatan" maxlength="300"
                    data-counter="hitungCatatan"
                    placeholder="Kebiasaan makan, filter, atau hal khusus..."><?= e($data['catatan']) ?></textarea>
          <div class="form-hint" id="hitungCatatan">0 / 300 karakter</div>
        </div>

        <div class="form-actions">
          <button class="btn-cyber" type="submit">SIMPAN SPESIMEN</button>
          <a class="btn-cyber ghost" href="<?= BASE_URL ?>ikan/index.php">BATAL</a>
        </div>
      </form>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// PANDUAN_KONDISI</h2>
    </div>
    <div class="panel-body">
      <div class="kv-list">
        <div class="kv-row"><span class="kv-key">SEHAT</span><span class="kv-val">Aktif, nafsu makan normal</span></div>
        <div class="kv-row"><span class="kv-key">PERHATIAN</span><span class="kv-val">Perlu dipantau, misal makan menurun</span></div>
        <div class="kv-row"><span class="kv-key">SAKIT</span><span class="kv-val">Perlu penanganan dan karantina</span></div>
      </div>

      <p class="form-hint" style="margin-top:16px;">
        Ukuran memakai sentimeter dan boleh ditulis dengan koma, misalnya 12,5.
        Umur diisi perkiraan dalam bulan.
      </p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
