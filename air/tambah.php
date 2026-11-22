<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();

$errors = [];
$input  = [
    'tanggal' => date('Y-m-d'),
    'ph'      => '7.0',
    'suhu_c'  => '26.0',
    'ammonia' => '0.00',
    'nitrit'  => '0.00',
    'nitrat'  => '0.00',
    'catatan' => '',
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

    if (empty($errors) && !$db_connected) {
        $errors[] = 'Database belum aktif. Nyalakan MySQL di XAMPP untuk menyimpan data.';
    }

    if (empty($errors)) {
        $sudah_ada = db_row(
            "SELECT id FROM water_params WHERE user_id = ? AND tanggal = ? LIMIT 1",
            'is',
            [(int)$user['id'], $input['tanggal']]
        );

        if ($sudah_ada) {
            $errors[] = 'Data tanggal ' . tanggal_indonesia($input['tanggal']) .
                        ' sudah ada. Buka data tersebut dan gunakan tombol EDIT.';
        } else {
            $berhasil = db_exec(
                "INSERT INTO water_params (user_id, tanggal, ph, suhu_c, ammonia, nitrit, nitrat, catatan)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    (int)$user['id'],
                    $input['tanggal'],
                    (float)$input['ph'],
                    (float)$input['suhu_c'],
                    (float)$input['ammonia'],
                    (float)$input['nitrit'],
                    (float)$input['nitrat'],
                    $input['catatan'] === '' ? null : $input['catatan'],
                ]
            );

            if ($berhasil) {
                set_flash('ok', 'Parameter air berhasil disimpan.');
                redirect(BASE_URL . 'air/index.php');
            }

            $errors[] = pesan_gagal('parameter air');
        }
    }
}

$page_title   = 'Tambah Parameter Air';
$page_eyebrow = 'KUALITAS AIR';
$page_desc    = 'Masukkan hasil tes air harian: pH, suhu, ammonia, nitrit, dan nitrat.';
$active       = 'air';

include __DIR__ . '/../includes/header.php';
?>

<div class="split">
  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// FORM_PARAMETER_AIR</h2>
      <span class="panel-sub">HARIAN</span>
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

      <form method="post" action="<?= BASE_URL ?>air/tambah.php" class="form-cyber">
        <?php include __DIR__ . '/form_fields.php'; ?>

        <div class="form-actions">
          <a class="btn-cyber ghost" href="<?= BASE_URL ?>air/index.php">BATAL</a>
          <button class="btn-cyber" type="submit">SIMPAN PARAMETER AIR</button>
        </div>
      </form>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// PANDUAN_BACA</h2>
    </div>
    <div class="panel-body">
      <p class="page-desc" style="margin-top:0;">
        Parameter air adalah indikator utama kesehatan ikan. Periksa setiap pagi
        sebelum memberi makan, terutama setelah water change atau penambahan obat.
      </p>
      <div class="kv-list">
        <div class="kv-row"><span class="kv-key">pH</span><span class="kv-val mono">6.5 - 8.0</span></div>
        <div class="kv-row"><span class="kv-key">Suhu</span><span class="kv-val mono">24 - 30 °C</span></div>
        <div class="kv-row"><span class="kv-key">Ammonia</span><span class="kv-val mono">&lt; 0.05 ppm</span></div>
        <div class="kv-row"><span class="kv-key">Nitrit</span><span class="kv-val mono">&lt; 0.10 ppm</span></div>
        <div class="kv-row"><span class="kv-key">Nitrat</span><span class="kv-val mono">&lt; 40 ppm</span></div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
