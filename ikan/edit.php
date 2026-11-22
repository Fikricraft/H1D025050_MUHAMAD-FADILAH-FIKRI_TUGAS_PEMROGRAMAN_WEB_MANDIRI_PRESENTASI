<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();
$id   = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'Data spesimen tidak valid.');
    redirect(BASE_URL . 'ikan/index.php');
}

$errors = [];
$pilihan_emblem = ['🐟', '🐠', '🐡', '🦈', '💧', '🌡️', '🧽', '💊', '⏱️'];
$label_emblem = [
    '🐟' => 'Ikan', '🐠' => 'Tropical', '🐡' => 'Buntal', '🦈' => 'Hiu',
    '💧' => 'Air', '🌡️' => 'Suhu', '🧽' => 'Bersih', '💊' => 'Obat',
    '⏱️' => 'Jadwal',
];

$ikan = $db_connected ? ambil_fish($id, (int)$user['id']) : null;

if (!$ikan && $db_connected) {
    set_flash('danger', 'Spesimen tidak ditemukan atau bukan milik akun ini.');
    redirect(BASE_URL . 'ikan/index.php');
}

if (!$db_connected) {
    foreach (demo_fishes() as $f) {
        if ((int)$f['id'] === $id) {
            $ikan = $f;
            break;
        }
    }
}

if (!$ikan) {
    set_flash('danger', 'Spesimen tidak ditemukan.');
    redirect(BASE_URL . 'ikan/index.php');
}

$data = $ikan;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['nama', 'jenis', 'ukuran_cm', 'umur_bulan', 'kondisi', 'catatan', 'emblem'] as $kolom) {
        $data[$kolom] = trim($_POST[$kolom] ?? '');
    }

    $ukuran = (float)str_replace(',', '.', (string)$data['ukuran_cm']);
    $umur   = (int)$data['umur_bulan'];

    if ($data['nama'] === '' || $data['jenis'] === '') {
        $errors[] = 'Nama dan jenis ikan wajib diisi.';
    }

    if ($ukuran <= 0 || $ukuran > 200) {
        $errors[] = 'Ukuran harus antara 0,1 sampai 200 cm.';
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
        $errors[] = 'Database belum aktif, perubahan tidak bisa disimpan.';
    }

    if (empty($errors)) {
        $berhasil = db_exec(
            "UPDATE fishes
             SET nama = ?, jenis = ?, ukuran_cm = ?, umur_bulan = ?,
                 kondisi = ?, catatan = ?, emblem = ?
             WHERE id = ? AND user_id = ?",
            [
                $data['nama'],
                $data['jenis'],
                $ukuran,
                $umur,
                $data['kondisi'],
                $data['catatan'] === '' ? null : $data['catatan'],
                $data['emblem'],
                $id,
                (int)$user['id'],
            ]
        );

        if ($berhasil) {
            set_flash('ok', 'Data spesimen "' . $data['nama'] . '" berhasil diperbarui.');
            redirect(BASE_URL . 'ikan/detail.php?id=' . $id);
        }

        $errors[] = pesan_gagal('spesimen');
    }
}

$page_title   = 'Edit Ikan';
$page_eyebrow = 'DATA IKAN';
$page_desc    = 'Perbaiki data spesimen, lalu simpan kembali.';
$active       = 'ikan';

include __DIR__ . '/../includes/header.php';
?>

<div class="split">
  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// EDIT_SPESIMEN</h2>
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

      <form method="post" action="<?= BASE_URL ?>ikan/edit.php?id=<?= $id ?>"
            class="form-cyber" data-warn-unsaved>
        <input type="hidden" name="id" value="<?= $id ?>">

        <div class="form-grid">
          <div class="form-row" style="margin:0;">
            <label class="form-label" for="nama">Nama spesimen</label>
            <input class="form-input" type="text" id="nama" name="nama"
                   value="<?= e($data['nama']) ?>" maxlength="60" required>
          </div>

          <div class="form-row" style="margin:0;">
            <label class="form-label" for="jenis">Jenis / spesies</label>
            <input class="form-input" type="text" id="jenis" name="jenis"
                   value="<?= e($data['jenis']) ?>" maxlength="60" required>
          </div>

          <div class="form-row" style="margin:0;">
            <label class="form-label" for="ukuran_cm">Ukuran (cm)</label>
            <input class="form-input" type="text" id="ukuran_cm" name="ukuran_cm"
                   value="<?= e($data['ukuran_cm']) ?>" required>
          </div>

          <div class="form-row" style="margin:0;">
            <label class="form-label" for="umur_bulan">Umur (bulan)</label>
            <input class="form-input" type="number" id="umur_bulan" name="umur_bulan"
                   value="<?= (int)$data['umur_bulan'] ?>" min="0" max="600" required>
          </div>
        </div>

        <div class="form-grid" style="margin-top:16px;">
          <div class="form-row" style="margin:0;">
            <label class="form-label" for="kondisi">Kondisi</label>
            <select class="form-select" id="kondisi" name="kondisi">
              <?php foreach (['SEHAT', 'PERHATIAN', 'SAKIT'] as $opsi) { ?>
                <option value="<?= $opsi ?>" <?= strtoupper((string)$data['kondisi']) === $opsi ? 'selected' : '' ?>>
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
                    data-counter="hitungCatatan"><?= e($data['catatan']) ?></textarea>
          <div class="form-hint" id="hitungCatatan">0 / 300 karakter</div>
        </div>

        <div class="form-actions">
          <button class="btn-cyber" type="submit">SIMPAN PERUBAHAN</button>
          <a class="btn-cyber ghost" href="<?= BASE_URL ?>ikan/detail.php?id=<?= $id ?>">BATAL</a>
        </div>
      </form>

      <form method="post" action="<?= BASE_URL ?>ikan/hapus.php"
            style="margin-top:18px;padding-top:16px;border-top:1px dashed var(--border-color);">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button class="btn-cyber danger" type="submit"
                data-confirm="Hapus spesimen <?= e($data['nama']) ?> secara permanen?">
          HAPUS SPESIMEN INI
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
        <div class="kv-row"><span class="kv-key">Nama</span><span class="kv-val"><?= e($ikan['nama']) ?></span></div>
        <div class="kv-row"><span class="kv-key">Jenis</span><span class="kv-val"><?= e($ikan['jenis']) ?></span></div>
        <div class="kv-row"><span class="kv-key">Ukuran</span><span class="kv-val"><?= number_format((float)$ikan['ukuran_cm'], 1, ',', '.') ?> cm</span></div>
        <div class="kv-row"><span class="kv-key">Umur</span><span class="kv-val"><?= (int)$ikan['umur_bulan'] ?> bulan</span></div>
        <div class="kv-row"><span class="kv-key">Kondisi</span><span class="kv-val"><?= e($ikan['kondisi']) ?></span></div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
