<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    set_flash('danger', 'ID spesimen tidak valid.');
    redirect(BASE_URL . 'ikan/index.php');
}

$ikan = $db_connected
    ? db_row(
        "SELECT id, nama, jenis, ukuran_cm, umur_bulan, kondisi, catatan, emblem, created_at
         FROM fishes WHERE id = ? AND user_id = ?",
        'ii',
        [$id, $user['id']]
    )
    : null;

if (!$ikan && !$db_connected) {
    foreach (demo_fishes() as $f) {
        if ((int)$f['id'] === $id) {
            $ikan = $f + ['created_at' => date('Y-m-d')];
            break;
        }
    }
}

if (!$ikan) {
    set_flash('danger', 'Spesimen tidak ditemukan atau bukan milik akun ini.');
    redirect(BASE_URL . 'ikan/index.php');
}

$riwayat = [];
if ($db_connected) {
    $riwayat = db_all(
        "SELECT l.tanggal, COUNT(*) AS jumlah
         FROM habit_logs l
         JOIN habits h ON h.id = l.habit_id
         WHERE h.user_id = ?
           AND l.tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
         GROUP BY l.tanggal
         ORDER BY l.tanggal ASC",
        'i',
        [$user['id']]
    );
}

$peta_riwayat = [];
foreach ($riwayat as $r) {
    $peta_riwayat[$r['tanggal']] = (int)$r['jumlah'];
}

$habit_teratas = [];
if ($db_connected) {
    $habit_teratas = db_all(
        "SELECT h.nama_habit, h.emblem, COUNT(l.id) AS total
         FROM habits h
         LEFT JOIN habit_logs l
                ON l.habit_id = h.id
               AND l.tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         WHERE h.user_id = ?
         GROUP BY h.id
         ORDER BY total DESC, h.nama_habit ASC
         LIMIT 6",
        'i',
        [$user['id']]
    );
}

$page_title   = $ikan['nama'];
$page_eyebrow = 'SPESIMEN / DETAIL';
$page_desc    = $ikan['jenis'] . ' · tercatat sejak ' . tanggal_indonesia($ikan['created_at'] ?? date('Y-m-d'));
$active       = 'ikan';

include __DIR__ . '/../includes/header.php';
?>

<div class="toolbar">
  <a class="btn-cyber ghost" href="<?= BASE_URL ?>ikan/index.php">&larr; SEMUA IKAN</a>
  <div class="toolbar-group">
    <a class="btn-cyber" href="<?= BASE_URL ?>ikan/edit.php?id=<?= (int)$ikan['id'] ?>">EDIT DATA</a>
  </div>
</div>

<div class="split">
  <div>
    <div class="panel">
      <div class="panel-head">
        <h2 class="panel-title">// PROFIL_SPESIMEN #<?= (int)$ikan['id'] ?></h2>
        <span class="badge <?= kelas_kondisi($ikan['kondisi']) ?>"><?= e($ikan['kondisi']) ?></span>
      </div>

      <div class="panel-body">
        <div style="display:flex;gap:18px;align-items:center;flex-wrap:wrap;margin-bottom:18px;">
          <div style="font-size:52px;line-height:1;"><?= e($ikan['emblem'] ?: '🐟') ?></div>
          <div>
            <div style="font-family:var(--font-display);font-size:32px;line-height:1;">
              <?= e($ikan['nama']) ?>
            </div>
            <div class="mono dim" style="font-size:11px;letter-spacing:.1em;">
              <?= e(strtoupper($ikan['jenis'])) ?>
            </div>
          </div>
        </div>

        <div class="kv-list">
          <div class="kv-row">
            <span class="kv-key">Ukuran</span>
            <span class="kv-val"><?= number_format((float)$ikan['ukuran_cm'], 1, ',', '.') ?> cm</span>
          </div>
          <div class="kv-row">
            <span class="kv-key">Umur</span>
            <span class="kv-val"><?= (int)$ikan['umur_bulan'] ?> bulan</span>
          </div>
          <div class="kv-row">
            <span class="kv-key">Kondisi</span>
            <span class="kv-val"><?= e($ikan['kondisi']) ?></span>
          </div>
          <div class="kv-row">
            <span class="kv-key">Catatan</span>
            <span class="kv-val"><?= e($ikan['catatan'] ?: '-') ?></span>
          </div>
        </div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head">
        <h2 class="panel-title">// CHECKLIST_7_HARI</h2>
        <span class="panel-sub">SEMUA SPESIMEN</span>
      </div>

      <div class="panel-body">
        <?php if (!$db_connected) { ?>
          <div class="flash flash-warn">
            <span class="flash-mark">▲</span> Grafik riwayat butuh MySQL aktif.
          </div>
        <?php } ?>

        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:8px;">
          <?php
            $maks = max(1, $peta_riwayat ? max($peta_riwayat) : 1);
            for ($i = 6; $i >= 0; $i--) {
                $tgl   = date('Y-m-d', strtotime("-$i day"));
                $jumlah = $peta_riwayat[$tgl] ?? 0;
                $tinggi = (int)round($jumlah / $maks * 100);
          ?>
            <div style="text-align:center;">
              <div style="height:110px;display:flex;align-items:flex-end;background:#061320;border:1px solid var(--border-color);">
                <div style="width:100%;height:<?= max(4, $tinggi) ?>%;background:linear-gradient(180deg,var(--brand-blue),rgba(0,210,255,.15));"></div>
              </div>
              <div class="mono" style="font-size:10px;color:var(--text-muted);margin-top:5px;">
                <?= date('d/m', strtotime($tgl)) ?>
              </div>
              <div class="mono" style="font-size:11px;color:var(--brand-cyan);"><?= $jumlah ?></div>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// HABIT_PALING_RUTIN</h2>
      <span class="panel-sub">30 HARI</span>
    </div>

    <div class="panel-body">
      <?php if (empty($habit_teratas)) { ?>
        <div class="empty-panel" style="border:none;padding:24px 8px;">
          <span class="empty-mark">☑</span>
          <h3>Belum ada data</h3>
          <p>Checklist habit di dashboard untuk mengisi statistik ini.</p>
        </div>
      <?php } else { ?>
        <?php
          $tertinggi = max(array_map(fn($h) => (int)$h['total'], $habit_teratas)) ?: 1;
          foreach ($habit_teratas as $h) {
              $lebar = (int)round((int)$h['total'] / $tertinggi * 100);
        ?>
          <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:5px;">
              <span><?= e($h['emblem']) ?> <?= e($h['nama_habit']) ?></span>
              <span class="mono" style="color:var(--brand-cyan);"><?= (int)$h['total'] ?>x</span>
            </div>
            <div class="bar-track" style="height:6px;">
              <div class="bar-fill" style="width:<?= max(3, $lebar) ?>%;"></div>
            </div>
          </div>
        <?php } ?>
      <?php } ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
