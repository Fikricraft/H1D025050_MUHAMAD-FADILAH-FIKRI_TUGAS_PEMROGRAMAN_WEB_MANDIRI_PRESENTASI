<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();
$id   = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'Data parameter air tidak valid.');
    redirect(BASE_URL . 'air/index.php');
}

$data_lama = ambil_water_param($id, (int)$user['id']);

if (!$data_lama) {
    set_flash('danger', 'Data parameter air tidak ditemukan atau bukan milik akun ini.');
    redirect(BASE_URL . 'air/index.php');
}

$errors = [];
$input  = [
    'tanggal' => $data_lama['tanggal'],
    'ph'      => (string)$data_lama['ph'],
    'suhu_c'  => (string)$data_lama['suhu_c'],
    'ammonia' => (string)$data_lama['ammonia'],
    'nitrit'  => (string)$data_lama['nitrit'],
    'nitrat'  => (string)$data_lama['nitrat'],
    'catatan' => (string)($data_lama['catatan'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (array_keys($input) as $kolom) {
        $input[$kolom] = trim($_POST[$kolom] ?? '');
    }

    if ($input['tanggal'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['tanggal'])) {
        $errors[] = 'Tanggal tes wajib diisi dengan format yang benar.';
    }

    if (!is_numeric($input['ph']) || $input['ph'] < 0 || $input['ph'] > 14) {
        $errors[] = 'pH harus angka antara 0 sampai 14.';
    }

    if (!is_numeric($input['suhu_c']) || $input['suhu_c'] < 0 || $input['suhu_c'] > 40) {
        $errors[] = 'Suhu harus angka antara 0 sampai 40°C.';
    }

    foreach (['ammonia' => 'Ammonia', 'nitrit' => 'Nitrit', 'nitrat' => 'Nitrat'] as $kolom => $label) {
        if (!is_numeric($input[$kolom]) || $input[$kolom] < 0) {
            $errors[] = $label . ' harus angka tidak negatif.';
        }
    }

    if (empty($errors)) {
        $bentrok = db_row(
            "SELECT id FROM water_params WHERE user_id = ? AND tanggal = ? AND id <> ? LIMIT 1",
            'isi',
            [(int)$user['id'], $input['tanggal'], $id]
        );

        if ($bentrok) {
            $errors[] = 'Sudah ada data lain pada tanggal tersebut. Pilih tanggal lain.';
        }
    }

    if (empty($errors) && !$db_connected) {
        $errors[] = 'Database belum aktif. Nyalakan MySQL di XAMPP untuk menyimpan perubahan.';
    }

    if (empty($errors)) {
        $berhasil = db_exec(
            "UPDATE water_params
             SET tanggal = ?, ph = ?, suhu_c = ?, ammonia = ?, nitrit = ?, nitrat = ?, catatan = ?
             WHERE id = ? AND user_id = ?",
            [
                $input['tanggal'],
                (float)$input['ph'],
                (float)$input['suhu_c'],
                (float)$input['ammonia'],
                (float)$input['nitrit'],
                (float)$input['nitrat'],
                $input['catatan'] === '' ? null : $input['catatan'],
                $id,
                (int)$user['id'],
            ]
        );

        if ($berhasil) {
            set_flash('ok', 'Parameter air berhasil diperbarui.');
            redirect(BASE_URL . 'air/index.php');
        }

        $errors[] = pesan_gagal('parameter air');
    }
}

$page_title   = 'Edit Parameter Air';
$page_eyebrow = 'KUALITAS AIR';
$page_desc    = 'Perbaiki hasil tes air pada tanggal ' . tanggal_indonesia($data_lama['tanggal']) . '.';
$active       = 'air';

include __DIR__ . '/../includes/header.php';
?>

<div class="split">
  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// FORM_EDIT_PARAMETER_AIR</h2>
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

      <form method="post" action="<?= BASE_URL ?>air/edit.php?id=<?= $id ?>" class="form-cyber">
        <input type="hidden" name="id" value="<?= $id ?>">
        <?php include __DIR__ . '/form_fields.php'; ?>

        <div class="form-actions">
          <a class="btn-cyber ghost" href="<?= BASE_URL ?>air/index.php">BATAL</a>
          <button class="btn-cyber" type="submit">SIMPAN PERUBAHAN</button>
        </div>
      </form>

      <form method="post" action="<?= BASE_URL ?>air/hapus.php"
            style="margin-top:18px;padding-top:16px;border-top:1px dashed var(--border-color);">
        <input type="hidden" name="id" value="<?= $id ?>">
        <button class="btn-cyber danger" type="submit"
                data-confirm="Hapus data parameter air tanggal <?= e($data_lama['tanggal']) ?>?">
          HAPUS DATA INI
        </button>
      </form>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// STATUS_DATA_INI</h2>
    </div>
    <div class="panel-body">
      <?php $status_lama = status_parameter_air($data_lama); ?>
      <div class="kv-list">
        <div class="kv-row"><span class="kv-key">pH</span><span class="kv-val mono"><?= e($data_lama['ph']) ?> &middot; <?= $status_lama['ph'] ?></span></div>
        <div class="kv-row"><span class="kv-key">Suhu</span><span class="kv-val mono"><?= e($data_lama['suhu_c']) ?> °C &middot; <?= $status_lama['suhu'] ?></span></div>
        <div class="kv-row"><span class="kv-key">Ammonia</span><span class="kv-val mono"><?= e($data_lama['ammonia']) ?> ppm &middot; <?= $status_lama['amonia'] ?></span></div>
        <div class="kv-row"><span class="kv-key">Nitrit</span><span class="kv-val mono"><?= e($data_lama['nitrit']) ?> ppm &middot; <?= $status_lama['nitrit'] ?></span></div>
        <div class="kv-row"><span class="kv-key">Nitrat</span><span class="kv-val mono"><?= e($data_lama['nitrat']) ?> ppm &middot; <?= $status_lama['nitrat'] ?></span></div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
