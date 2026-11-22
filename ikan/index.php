<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user   = current_user();
$fishes = ambil_fishes($user['id']);

$total_ikan   = count($fishes);
$total_ukuran = 0;
$jumlah_sehat = 0;

foreach ($fishes as $f) {
    $total_ukuran += (float)$f['ukuran_cm'];

    if (strtoupper($f['kondisi']) === 'SEHAT') {
        $jumlah_sehat++;
    }
}

$rata_ukuran = $total_ikan > 0 ? $total_ukuran / $total_ikan : 0;
$persen_sehat = $total_ikan > 0 ? (int)round($jumlah_sehat / $total_ikan * 100) : 0;

$page_title   = 'Data Ikan';
$page_eyebrow = 'AQUARIUM INVENTORY';
$page_desc    = 'Daftar spesimen yang dipantau beserta kondisi terkini.';
$active       = 'ikan';

include __DIR__ . '/../includes/header.php';
?>

<div class="stat-grid">
  <div class="stat-box">
    <div class="stat-label">Total Spesimen</div>
    <div class="stat-number"><?= $total_ikan ?></div>
    <div class="stat-note">TERDAFTAR DI AKUARIUM</div>
  </div>

  <div class="stat-box">
    <div class="stat-label">Rata-rata Ukuran</div>
    <div class="stat-number"><?= number_format($rata_ukuran, 1, ',', '.') ?><small> cm</small></div>
    <div class="stat-note">RATA-RATA SPESIMEN</div>
  </div>

  <div class="stat-box">
    <div class="stat-label">Kondisi Sehat</div>
    <div class="stat-number"><?= $persen_sehat ?><small> %</small></div>
    <div class="stat-note <?= $persen_sehat === 100 ? '' : 'dim' ?>">
      <?= $jumlah_sehat ?> DARI <?= $total_ikan ?> SPESIMEN
    </div>
  </div>
</div>

<div class="toolbar">
  <div class="toolbar-group">
    <input class="form-input" type="text" placeholder="Cari nama / jenis / kondisi..."
           style="max-width:300px;" data-table-filter="#tabelIkan">
    <span class="panel-sub" data-filter-empty style="display:none;color:#ffbf47;">
      Tidak ada data yang cocok.
    </span>
  </div>

  <a class="btn-cyber" href="<?= BASE_URL ?>ikan/tambah.php">+ TAMBAH IKAN</a>
</div>

<div class="panel">
  <div class="panel-head">
    <h2 class="panel-title">// DAFTAR_SPESIMEN</h2>
    <span class="panel-sub"><?= $total_ikan ?> BARIS</span>
  </div>

  <div class="panel-body tight">
    <?php if ($total_ikan === 0) { ?>
      <div class="empty-panel" style="border:none;">
        <span class="empty-mark">⚠</span>
        <h3>Belum ada ikan terdaftar</h3>
        <p>Klik tombol TAMBAH IKAN untuk mendaftarkan spesimen pertama.</p>
      </div>
    <?php } else { ?>
      <div class="table-wrap">
        <table class="table-cyber" id="tabelIkan">
          <thead>
            <tr>
              <th width="50">ID</th>
              <th width="46">IKON</th>
              <th>Nama Spesimen</th>
              <th>Jenis</th>
              <th class="num">Ukuran</th>
              <th class="num">Umur</th>
              <th>Kondisi</th>
              <th width="240">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($fishes as $f) { ?>
              <tr>
                <td class="mono muted">#<?= (int)$f['id'] ?></td>
                <td class="center" style="font-size:18px;"><?= e($f['emblem'] ?: '🐟') ?></td>
                <td>
                  <b><?= e($f['nama']) ?></b>
                  <?php if (!empty($f['catatan'])) { ?>
                    <div class="muted"><?= e($f['catatan']) ?></div>
                  <?php } ?>
                </td>
                <td class="muted"><?= e($f['jenis']) ?></td>
                <td class="num"><?= number_format((float)$f['ukuran_cm'], 1, ',', '.') ?> cm</td>
                <td class="num"><?= (int)$f['umur_bulan'] ?> bln</td>
                <td>
                  <span class="badge <?= kelas_kondisi($f['kondisi']) ?>">
                    <?= e($f['kondisi']) ?>
                  </span>
                </td>
                <td>
                  <div class="aksi">
                    <a class="btn-mini" href="<?= BASE_URL ?>ikan/detail.php?id=<?= (int)$f['id'] ?>">LIHAT</a>
                    <a class="btn-mini ok" href="<?= BASE_URL ?>ikan/edit.php?id=<?= (int)$f['id'] ?>">EDIT</a>

                    <form method="post" action="<?= BASE_URL ?>ikan/hapus.php" style="display:inline;">
                      <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                      <button class="btn-mini danger" type="submit"
                              data-confirm="Hapus spesimen '<?= e($f['nama']) ?>' dari daftar?>
                        HAPUS
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4">TOTAL <?= $total_ikan ?> SPESIMEN</td>
              <td class="num"><?= number_format($total_ukuran, 1, ',', '.') ?> cm</td>
              <td colspan="3" class="muted">rata-rata <?= number_format($rata_ukuran, 1, ',', '.') ?> cm</td>
            </tr>
          </tfoot>
        </table>
      </div>
    <?php } ?>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
