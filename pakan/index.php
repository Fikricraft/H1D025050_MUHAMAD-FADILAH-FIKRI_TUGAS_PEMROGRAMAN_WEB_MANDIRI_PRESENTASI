<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user     = current_user();
$feedings = ambil_feedings((int)$user['id'], 200);
$ringkas  = ringkasan_pakan($feedings);
$jenis    = daftar_jenis_pakan((int)$user['id']);

$pemakaian_jenis = [];
foreach ($feedings as $f) {
    $nama = $f['jenis_pakan'];
    $pemakaian_jenis[$nama] = ($pemakaian_jenis[$nama] ?? 0) + 1;
}

$page_title   = 'Data Pakan';
$page_eyebrow = 'CATATAN PAKAN HARIAN';
$page_desc    = 'Mencatat jenis pakan, jumlah, dan apakah pakan habis dimakan.';
$active       = 'pakan';

include __DIR__ . '/../includes/header.php';
?>

<div class="stat-grid">
  <div class="stat-box">
    <div class="stat-label">Total Catatan Pakan</div>
    <div class="stat-number"><?= count($feedings) ?></div>
    <div class="stat-note">CATATAN TERSIMPAN</div>
  </div>

  <div class="stat-box">
    <div class="stat-label">Jumlah Hari Ini</div>
    <div class="stat-number"><?= number_format($ringkas['hari_ini'], 2, ',', '.') ?><small> butir</small></div>
    <div class="stat-note">PAKAN MASUK</div>
  </div>

  <div class="stat-box">
    <div class="stat-label">Habis Dimakan</div>
    <div class="stat-number"><?= $ringkas['habis'] ?></div>
    <div class="stat-note">NAFSU MAKAN BAGUS</div>
  </div>

  <div class="stat-box">
    <div class="stat-label">Sisa Pakan</div>
    <div class="stat-number"><?= $ringkas['sisa'] ?></div>
    <div class="stat-note <?= $ringkas['sisa'] > 0 ? 'dim' : '' ?>">PERLU DITINJAU</div>
  </div>
</div>

<div class="toolbar">
  <div class="toolbar-group">
    <input class="form-input" type="text" placeholder="Cari tanggal / jenis / ikan..."
           style="max-width:280px;" data-table-filter="#tabelPakan">
    <span class="panel-sub" data-filter-empty style="display:none;color:#ffbf47;">
      Tidak ada data yang cocok.
    </span>
  </div>

  <a class="btn-cyber" href="<?= BASE_URL ?>pakan/tambah.php">+ TAMBAH PAKAN</a>
</div>

<div class="panel">
  <div class="panel-head">
    <h2 class="panel-title">// RIWAYAT_PAKAN</h2>
    <span class="panel-sub"><?= count($feedings) ?> BARIS</span>
  </div>

  <div class="panel-body tight">
    <?php if (empty($feedings)) { ?>
      <div class="empty-panel" style="border:none;">
        <span class="empty-mark">🍽</span>
        <h3>Belum ada catatan pakan</h3>
        <p>Klik tombol TAMBAH PAKAN untuk mencatat pemberian pakan pertama.</p>
      </div>
    <?php } else { ?>
      <div class="table-wrap">
        <table class="table-cyber" id="tabelPakan">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Waktu</th>
              <th>Ikan</th>
              <th>Jenis Pakan</th>
              <th class="num">Jumlah</th>
              <th>Status</th>
              <th>Catatan</th>
              <th width="140">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($feedings as $f) { ?>
              <tr>
                <td class="mono"><?= e($f['tanggal']) ?></td>
                <td class="mono"><?= e(jam_ringkas($f['waktu'])) ?></td>
                <td><?= e($f['nama_ikan'] ?: 'Umum') ?></td>
                <td><b><?= e($f['jenis_pakan']) ?></b></td>
                <td class="num"><?= number_format((float)$f['jumlah'], 2, ',', '.') ?> <?= e($f['satuan']) ?></td>
                <td>
                  <span class="badge <?= (int)$f['habis'] === 1 ? 'badge-ok' : 'badge-warn' ?>">
                    <?= (int)$f['habis'] === 1 ? 'HABIS' : 'SISA' ?>
                  </span>
                </td>
                <td class="muted"><?= e($f['catatan'] ?: '-') ?></td>
                <td>
                  <div class="aksi">
                    <a class="btn-mini ok" href="<?= BASE_URL ?>pakan/edit.php?id=<?= (int)$f['id'] ?>">EDIT</a>

                    <form method="post" action="<?= BASE_URL ?>pakan/hapus.php" style="display:inline;">
                      <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
                      <button class="btn-mini danger" type="submit"
                              data-confirm="Hapus catatan pakan <?= e($f['jenis_pakan']) ?> tanggal <?= e($f['tanggal']) ?>?">
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
              <td colspan="4">TOTAL PAKAN TERCATAT</td>
              <td class="num"><?= number_format($ringkas['total'], 2, ',', '.') ?></td>
              <td colspan="3" class="muted">dari <?= count($feedings) ?> catatan</td>
            </tr>
          </tfoot>
        </table>
      </div>
    <?php } ?>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <h2 class="panel-title">// JENIS_PAKAN</h2>
    <span class="panel-sub"><?= count($jenis) ?> PILIHAN</span>
  </div>
  <div class="panel-body">
    <p class="page-desc" style="margin-top:0;">
      Jenis pakan di bawah ini otomatis muncul sebagai pilihan saat mencatat pakan.
      Ingin jenis lain? Tulis sendiri pada formulir, sistem akan mengingatnya.
    </p>

    <div class="kv-list">
      <?php foreach ($jenis as $jp) { ?>
        <div class="kv-row">
          <span class="kv-key" style="text-transform:none;letter-spacing:0;"><?= e($jp) ?></span>
          <span class="kv-val mono">
            <?= isset($pemakaian_jenis[$jp]) ? (int)$pemakaian_jenis[$jp] . 'x dipakai' : 'belum dipakai' ?>
          </span>
        </div>
      <?php } ?>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <h2 class="panel-title">// CARA_PAKAI</h2>
  </div>
  <div class="panel-body">
    <p class="page-desc" style="margin-top:0;">
      Catat setiap pemberian pakan. Tandai <b>pakan habis</b> bila seluruh pakan
      dimakan dalam waktu dua menit. Bila sering tersisa, kurangi jumlah pakan
      berikutnya agar kualitas air tetap terjaga.
    </p>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
