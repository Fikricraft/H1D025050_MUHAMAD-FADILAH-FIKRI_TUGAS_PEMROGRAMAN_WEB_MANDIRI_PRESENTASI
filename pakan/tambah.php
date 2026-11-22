<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user   = current_user();
$fishes = ambil_fishes($user['id']);

$jenis_pakan     = daftar_jenis_pakan($user['id']);
$satuan_pilihan  = daftar_satuan();

$errors = [];
$input  = [
    'tanggal'     => date('Y-m-d'),
    'waktu'       => date('H:i'),
    'fish_id'     => '',
    'jenis_pakan' => '',
    'jumlah'      => '1',
    'satuan'      => 'butir',
    'habis'       => '1',
    'catatan'     => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pilih_jenis = trim($_POST['jenis_pakan_pilih'] ?? '');
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
            $errors[] = 'Database belum aktif. Nyalakan MySQL di XAMPP untuk menyimpan data.';
        } else {
            $fish_id = $input['fish_id'] === '' ? null : (int)$input['fish_id'];

            $berhasil = db_exec(
                "INSERT INTO feedings
                    (user_id, fish_id, tanggal, waktu, jenis_pakan, jumlah, satuan, habis, catatan)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    (int)$user['id'],
                    $fish_id,
                    $input['tanggal'],
                    $input['waktu'] . ':00',
                    $input['jenis_pakan'],
                    (float)$input['jumlah'],
                    $input['satuan'],
                    (int)$input['habis'],
                    $input['catatan'] === '' ? null : $input['catatan'],
                ]
            );

            if ($berhasil) {
                simpan_jenis_pakan($user['id'], $input['jenis_pakan']);
                set_flash('ok', 'Catatan pakan berhasil disimpan.');
                redirect(BASE_URL . 'pakan/index.php');
            }

            $errors[] = pesan_gagal('pakan');
        }
    }
}

$page_title   = 'Tambah Pakan';
$page_eyebrow = 'CATAT PAKAN';
$page_desc    = 'Catat jenis pakan, jumlah, waktu, dan apakah pakan habis dimakan.';
$active       = 'pakan';

include __DIR__ . '/../includes/header.php';
?>

<div class="split">
  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// FORM_TAMBAH_PAKAN</h2>
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

      <form method="post" action="<?= BASE_URL ?>pakan/tambah.php" class="form-cyber">
        <?php include __DIR__ . '/form_fields.php'; ?>

        <div class="form-actions">
          <a class="btn-cyber ghost" href="<?= BASE_URL ?>pakan/index.php">BATAL</a>
          <button class="btn-cyber" type="submit">SIMPAN CATATAN PAKAN</button>
        </div>
      </form>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// PANDUAN_PAKAN</h2>
    </div>
    <div class="panel-body">
      <p class="page-desc" style="margin-top:0;">
        Amati apakah pakan habis dalam dua menit. Banyak sisa pakan menandakan
        jumlah berlebih dan dapat menurunkan kualitas air.
      </p>
      <div class="kv-list">
        <div class="kv-row">
          <span class="kv-key">Pakan habis</span>
          <span class="kv-val mono">Nafsu makan normal</span>
        </div>
        <div class="kv-row">
          <span class="kv-key">Pakan sisa</span>
          <span class="kv-val mono">Kurangi porsi berikutnya</span>
        </div>
        <div class="kv-row">
          <span class="kv-key">Frekuensi</span>
          <span class="kv-val mono">2 kali sehari cukup</span>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
