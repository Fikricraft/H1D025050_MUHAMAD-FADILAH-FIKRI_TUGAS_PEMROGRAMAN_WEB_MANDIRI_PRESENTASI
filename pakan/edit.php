<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();
$id   = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'Catatan pakan tidak valid.');
    redirect(BASE_URL . 'pakan/index.php');
}

$feeding = ambil_feeding($id, (int)$user['id']);

if (!$feeding) {
    set_flash('danger', 'Catatan pakan tidak ditemukan atau bukan milik akun ini.');
    redirect(BASE_URL . 'pakan/index.php');
}

$fishes          = ambil_fishes($user['id']);
$jenis_pakan     = daftar_jenis_pakan($user['id']);
$satuan_pilihan  = daftar_satuan();

$errors = [];
$input  = [
    'tanggal'     => $feeding['tanggal'],
    'waktu'       => jam_ringkas($feeding['waktu']),
    'fish_id'     => (string)($feeding['fish_id'] ?? ''),
    'jenis_pakan' => $feeding['jenis_pakan'],
    'jumlah'      => rtrim(rtrim(number_format((float)$feeding['jumlah'], 2, '.', ''), '0'), '.'),
    'satuan'      => $feeding['satuan'],
    'habis'       => (string)(int)$feeding['habis'],
    'catatan'     => (string)($feeding['catatan'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pilih_jenis  = trim($_POST['jenis_pakan_pilih'] ?? '');
    $pilih_satuan = trim($_POST['satuan_pilih'] ?? '');

    $input['tanggal']     = trim($_POST['tanggal'] ?? '');
    $input['waktu']       = trim($_POST['waktu'] ?? '');
    $input['fish_id']     = trim($_POST['fish_id'] ?? '');
    $input['jenis_pakan'] = $pilih_jenis === '__baru'
        ? trim($_POST['jenis_pakan_baru'] ?? '')
        : $pilih_jenis;
    $input['jumlah']      = trim($_POST['jumlah'] ?? '');
    $input['satuan']      = $pilih_satuan === '__baru'
        ? trim($_POST['satuan_baru'] ?? '')
        : ($pilih_satuan !== '' ? $pilih_satuan : 'butir');
    $input['habis']       = isset($_POST['habis']) ? '1' : '0';
    $input['catatan']     = trim($_POST['catatan'] ?? '');

    if ($input['tanggal'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['tanggal'])) {
        $errors[] = 'Tanggal makan wajib diisi dengan format yang benar.';
    }

    if ($input['waktu'] === '' || !preg_match('/^\d{2}:\d{2}$/', $input['waktu'])) {
        $errors[] = 'Waktu makan wajib diisi dengan format jam HH:MM.';
    }

    if ($input['jenis_pakan'] === '') {
        $errors[] = 'Jenis pakan wajib dipilih atau ditulis.';
    } elseif (mb_strlen($input['jenis_pakan']) > 60) {
        $errors[] = 'Jenis pakan maksimal 60 karakter.';
    }

    if ($input['satuan'] === '') {
        $errors[] = 'Satuan pakan wajib diisi.';
    } elseif (mb_strlen($input['satuan']) > 20) {
        $errors[] = 'Satuan maksimal 20 karakter.';
    }

    if (!is_numeric($input['jumlah']) || (float)$input['jumlah'] <= 0 || (float)$input['jumlah'] > 9999) {
        $errors[] = 'Jumlah pakan harus berupa angka lebih dari nol.';
    }

    if ($input['fish_id'] !== '') {
        $milik_user = false;

        foreach ($fishes as $f) {
            if ((string)$f['id'] === $input['fish_id']) {
                $milik_user = true;
                break;
            }
        }

        if (!$milik_user) {
            $errors[] = 'Ikan yang dipilih tidak ditemukan pada akun ini.';
        }
    }

    if (empty($errors)) {
        if (!$db_connected) {
            $errors[] = 'Database belum aktif. Nyalakan MySQL di XAMPP untuk menyimpan perubahan.';
        } else {
            $fish_id = $input['fish_id'] === '' ? null : (int)$input['fish_id'];

            $berhasil = db_exec(
                "UPDATE feedings
                 SET fish_id = ?, tanggal = ?, waktu = ?, jenis_pakan = ?,
                     jumlah = ?, satuan = ?, habis = ?, catatan = ?
                 WHERE id = ? AND user_id = ?",
                [
                    $fish_id,
                    $input['tanggal'],
                    $input['waktu'] . ':00',
                    $input['jenis_pakan'],
                    (float)$input['jumlah'],
                    $input['satuan'],
                    (int)$input['habis'],
                    $input['catatan'] === '' ? null : $input['catatan'],
                    $id,
                    (int)$user['id'],
                ]
            );

            if ($berhasil) {
                simpan_jenis_pakan($user['id'], $input['jenis_pakan']);
                set_flash('ok', 'Catatan pakan berhasil diperbarui.');
                redirect(BASE_URL . 'pakan/index.php');
            }

            $errors[] = pesan_gagal('pakan');
        }
    }
}

$page_title   = 'Edit Pakan';
$page_eyebrow = 'UBAH CATATAN PAKAN';
$page_desc    = 'Perbaiki data pakan yang sudah tercatat, lalu simpan kembali.';
$active       = 'pakan';

include __DIR__ . '/../includes/header.php';
?>

<div class="split">
  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// FORM_EDIT_PAKAN</h2>
      <span class="panel-sub">CATATAN #<?= $id ?></span>
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

      <form method="post" action="<?= BASE_URL ?>pakan/edit.php?id=<?= $id ?>" class="form-cyber">
        <input type="hidden" name="id" value="<?= $id ?>">
        <?php include __DIR__ . '/form_fields.php'; ?>

        <div class="form-actions">
          <a class="btn-cyber ghost" href="<?= BASE_URL ?>pakan/index.php">BATAL</a>
          <button class="btn-cyber" type="submit">SIMPAN PERUBAHAN</button>
        </div>
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
        <div class="kv-row"><span class="kv-key">Tanggal</span><span class="kv-val mono"><?= e($feeding['tanggal']) ?></span></div>
        <div class="kv-row"><span class="kv-key">Waktu</span><span class="kv-val mono"><?= e(jam_ringkas($feeding['waktu'])) ?></span></div>
        <div class="kv-row"><span class="kv-key">Jenis pakan</span><span class="kv-val"><?= e($feeding['jenis_pakan']) ?></span></div>
        <div class="kv-row"><span class="kv-key">Jumlah</span><span class="kv-val mono"><?= number_format((float)$feeding['jumlah'], 2, ',', '.') ?> <?= e($feeding['satuan']) ?></span></div>
        <div class="kv-row"><span class="kv-key">Status</span><span class="kv-val"><?= (int)$feeding['habis'] === 1 ? 'Habis dimakan' : 'Ada sisa' ?></span></div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
