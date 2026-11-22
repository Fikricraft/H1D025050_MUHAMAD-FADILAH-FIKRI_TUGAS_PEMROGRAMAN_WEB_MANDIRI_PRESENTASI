<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user   = current_user();
$daftar = ambil_water_params((int)$user['id'], 30);
$params = $daftar[0] ?? null;
$grafik = ambil_grafik_air((int)$user['id']);
$status = $params ? status_parameter_air($params) : [];

$page_title   = 'Parameter Air';
$page_eyebrow = 'KUALITAS AIR';
$page_desc    = 'Pantau pH, suhu, ammonia, nitrit, dan nitrat. Status dihitung otomatis berdasarkan batas aman umum.';
$active       = 'air';

include __DIR__ . '/../includes/header.php';
?>

<div class="toolbar">
  <div class="toolbar-group">
    <input class="form-input" type="text" placeholder="Cari tanggal / catatan..."
           style="max-width:280px;" data-table-filter="#tabelAir">
    <span class="panel-sub" data-filter-empty style="display:none;color:#ffbf47;">
      Tidak ada data yang cocok.
    </span>
  </div>

  <a class="btn-cyber" href="<?= BASE_URL ?>air/tambah.php">+ TAMBAH PARAMETER AIR</a>
</div>

<?php if (empty($params)) { ?>
  <div class="panel">
    <div class="panel-body">
      <div class="empty-panel" style="border:none;">
        <span class="empty-mark">💧</span>
        <h3>Belum ada data parameter air</h3>
        <p>Tambahkan hasil tes air pertama Anda untuk mulai memantau kualitas air.</p>
        <a class="btn-cyber" href="<?= BASE_URL ?>air/tambah.php" style="margin-top:16px;">+ TAMBAH PARAMETER AIR</a>
      </div>
    </div>
  </div>
<?php } else { ?>

  <div class="stat-grid">
    <div class="stat-box">
      <div class="stat-label">pH</div>
      <div class="stat-number"><?= e($params['ph']) ?></div>
      <div class="stat-note <?= $status['ph'] === 'AMAN' ? '' : 'dim' ?>">
        <?= $status['ph'] ?> | RANGE 6.5 - 8.0
      </div>
    </div>

    <div class="stat-box">
      <div class="stat-label">Suhu Air</div>
      <div class="stat-number"><?= e($params['suhu_c']) ?><small> °C</small></div>
      <div class="stat-note <?= $status['suhu'] === 'AMAN' ? '' : 'dim' ?>">
        <?= $status['suhu'] ?> | RANGE 24 - 30
      </div>
    </div>

    <div class="stat-box">
      <div class="stat-label">Ammonia</div>
      <div class="stat-number"><?= e($params['ammonia']) ?><small> ppm</small></div>
      <div class="stat-note <?= $status['amonia'] === 'AMAN' ? '' : 'dim' ?>">
        <?= $status['amonia'] ?> | MAX 0.05
      </div>
    </div>

    <div class="stat-box">
      <div class="stat-label">Nitrit</div>
      <div class="stat-number"><?= e($params['nitrit']) ?><small> ppm</small></div>
      <div class="stat-note <?= $status['nitrit'] === 'AMAN' ? '' : 'dim' ?>">
        <?= $status['nitrit'] ?> | MAX 0.10
      </div>
    </div>

    <div class="stat-box">
      <div class="stat-label">Nitrat</div>
      <div class="stat-number"><?= e($params['nitrat']) ?><small> ppm</small></div>
      <div class="stat-note <?= $status['nitrat'] === 'AMAN' ? '' : 'dim' ?>">
        <?= $status['nitrat'] ?> | MAX 40
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// TREN_7_HARI [pH DAN SUHU]</h2>
      <span class="panel-sub"><?= count($grafik) ?> DATA TERAKHIR</span>
    </div>
    <div class="panel-body">
      <?php if (empty($grafik)) { ?>
        <div class="empty-panel" style="border:none;padding:26px 8px;">
          <span class="empty-mark">📊</span>
          <h3>Belum ada data grafik</h3>
          <p>Tambahkan parameter air selama 7 hari terakhir untuk melihat tren.</p>
        </div>
      <?php } else { ?>
        <div class="chart-area">
          <?php foreach ($grafik as $g) { ?>
            <div class="chart-col">
              <div class="chart-bar-wrap">
                <div class="chart-bar ph" style="height:<?= min(100, max(0, ((float)$g['ph'] - 5.0) / 4.0 * 100)) ?>%"></div>
                <div class="chart-bar suhu" style="height:<?= min(100, max(0, ((float)$g['suhu_c'] - 15.0) / 20.0 * 100)) ?>%"></div>
              </div>
              <div class="chart-label"><?= date('d/m', strtotime($g['tanggal'])) ?></div>
              <div class="chart-value"><?= e($g['ph']) ?> | <?= e($g['suhu_c']) ?>°C</div>
            </div>
          <?php } ?>
        </div>
        <div class="chart-legend">
          <span class="legend-item"><span class="legend-dot ph"></span> pH</span>
          <span class="legend-item"><span class="legend-dot suhu"></span> Suhu (°C)</span>
        </div>
      <?php } ?>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// RIWAYAT_PARAMETER_AIR</h2>
      <span class="panel-sub"><?= count($daftar) ?> RECORD</span>
    </div>
    <div class="panel-body tight">
      <div class="table-wrap">
        <table class="table-cyber" id="tabelAir">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th class="num">pH</th>
              <th class="num">Suhu</th>
              <th class="num">Ammonia</th>
              <th class="num">Nitrit</th>
              <th class="num">Nitrat</th>
              <th>Status</th>
              <th>Catatan</th>
              <th width="140">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($daftar as $w) {
                $st = status_parameter_air($w);
                $aman = count(array_filter($st, fn($v) => $v === 'AMAN'));
                $bahaya = in_array('BAHAYA', $st, true);
            ?>
              <tr>
                <td class="mono"><?= e($w['tanggal']) ?></td>
                <td class="num"><?= e($w['ph']) ?></td>
                <td class="num"><?= e($w['suhu_c']) ?>°C</td>
                <td class="num"><?= e($w['ammonia']) ?></td>
                <td class="num"><?= e($w['nitrit']) ?></td>
                <td class="num"><?= e($w['nitrat']) ?></td>
                <td>
                  <span class="badge <?= $bahaya ? 'badge-danger' : ($aman === 5 ? 'badge-ok' : 'badge-warn') ?>">
                    <?= $bahaya ? 'BAHAYA' : ($aman === 5 ? 'AMAN' : 'PERHATIAN') ?>
                  </span>
                  <div class="muted mono" style="font-size:10px;margin-top:3px;"><?= $aman ?>/5 parameter aman</div>
                </td>
                <td class="muted"><?= e($w['catatan'] ?: '-') ?></td>
                <td>
                  <div class="aksi">
                    <a class="btn-mini ok" href="<?= BASE_URL ?>air/edit.php?id=<?= (int)$w['id'] ?>">EDIT</a>

                    <form method="post" action="<?= BASE_URL ?>air/hapus.php" style="display:inline;">
                      <input type="hidden" name="id" value="<?= (int)$w['id'] ?>">
                      <button class="btn-mini danger" type="submit"
                              data-confirm="Hapus data parameter air tanggal <?= e($w['tanggal']) ?>?">
                        HAPUS
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php } ?>

<div class="panel">
  <div class="panel-head">
    <h2 class="panel-title">// BATAS_AMAN_UNTUK_IKAN_HIAS</h2>
  </div>
  <div class="panel-body">
    <ul style="margin:0;padding-left:20px;line-height:1.8;color:var(--text-muted);font-size:13px;">
      <li>pH ideal berkisar antara 6.5 sampai 8.0 untuk sebagian besar ikan hias.</li>
      <li>Suhu air ideal 24°C sampai 30°C, tergantung spesies.</li>
      <li>Ammonia harus di bawah 0.05 ppm, bila lebih tinggi lakukan water change.</li>
      <li>Nitrit harus di bawah 0.10 ppm, bila lebih tinggi periksa filter biologis.</li>
      <li>Nitrat sebaiknya di bawah 40 ppm, bila lebih tinggi ganti sebagian air.</li>
    </ul>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
