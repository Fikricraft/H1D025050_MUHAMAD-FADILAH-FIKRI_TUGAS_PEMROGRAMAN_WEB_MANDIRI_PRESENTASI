<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) || strtotime($tanggal) === false) {
    $tanggal = date('Y-m-d');
}

$habits = ambil_habits($user['id']);

$selesai = [];
if ($db_connected) {
    $rows = db_all(
        "SELECT l.id, l.habit_id, l.completed_at, h.nama_habit, h.emblem, h.kategori
         FROM habit_logs l
         JOIN habits h ON h.id = l.habit_id
         WHERE h.user_id = ? AND l.tanggal = ?
         ORDER BY l.completed_at ASC",
        'is',
        [$user['id'], $tanggal]
    );

    foreach ($rows as $r) {
        $selesai[(int)$r['habit_id']] = $r;
    }
}

$stat = statistik_harian($habits, array_keys($selesai));

$sebelumnya = date('Y-m-d', strtotime($tanggal . ' -1 day'));
$berikutnya = date('Y-m-d', strtotime($tanggal . ' +1 day'));
$hari_ini   = date('Y-m-d');

$page_title   = 'Riwayat Checklist';
$page_eyebrow = 'LOG ARCHIVE';
$page_desc    = 'Pencatatan centang per tanggal. Satu protokol hanya bisa dicentang sekali per hari.';
$active       = 'log';

include __DIR__ . '/../includes/header.php';
?>

<div class="toolbar">
  <div class="toolbar-group">
    <a class="btn-mini" href="?tanggal=<?= $sebelumnya ?>">&larr; HARI SEBELUMNYA</a>

    <form method="get" action="<?= BASE_URL ?>log/index.php" style="display:flex;gap:6px;">
      <input class="form-input" type="date" name="tanggal" value="<?= e($tanggal) ?>"
             style="max-width:170px;">
      <button class="btn-mini" type="submit">TAMPILKAN</button>
    </form>

    <a class="btn-mini" href="?tanggal=<?= $berikutnya ?>">HARI BERIKUTNYA &rarr;</a>
    <?php if ($tanggal !== $hari_ini) { ?>
      <a class="btn-mini ok" href="?tanggal=<?= $hari_ini ?>">HARI INI</a>
    <?php } ?>
  </div>

  <span class="meta-chip mono">
    <?= nama_hari($tanggal) ?>, <?= tanggal_indonesia($tanggal) ?>
  </span>
</div>

<div class="stat-grid">
  <div class="stat-box">
    <div class="stat-label">Protokol</div>
    <div class="stat-number"><?= $stat['total'] ?></div>
    <div class="stat-note dim">TOTAL HABIT</div>
  </div>
  <div class="stat-box">
    <div class="stat-label">Selesai</div>
    <div class="stat-number"><?= $stat['selesai'] ?><small> / <?= $stat['total'] ?></small></div>
    <div class="stat-note">DICENTANG PADA TANGGAL INI</div>
  </div>
  <div class="stat-box">
    <div class="stat-label">Persentase</div>
    <div class="stat-number"><?= $stat['persen'] ?><small> %</small></div>
    <div class="bar-track" style="margin-top:8px;">
      <div class="bar-fill" style="width:<?= $stat['persen'] ?>%;"></div>
    </div>
  </div>
</div>

<div class="split">
  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// CHECKLIST_HARIAN</h2>
      <span class="panel-sub"><?= e($tanggal) ?></span>
    </div>

    <div class="panel-body tight">
      <?php if (count($habits) === 0) { ?>
        <div class="empty-panel" style="border:none;">
          <span class="empty-mark">☑</span>
          <h3>Belum ada habit</h3>
          <p>Buat habit terlebih dahulu di menu HABIT.</p>
        </div>
      <?php } else { ?>
        <div class="table-wrap">
          <table class="table-cyber">
            <thead>
              <tr>
                <th width="46">IKON</th>
                <th>Habit</th>
                <th>Kategori</th>
                <th>Jadwal</th>
                <th>Status</th>
                <th width="120">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($habits as $h) {
                  $sudah = isset($selesai[(int)$h['id']]);
              ?>
                <tr>
                  <td class="center" style="font-size:18px;"><?= e($h['emblem']) ?></td>
                  <td><b><?= e($h['nama_habit']) ?></b></td>
                  <td><span class="badge badge-info"><?= e($h['kategori']) ?></span></td>
                  <td class="muted"><?= e($h['waktu']) ?></td>
                  <td>
                    <?php if ($sudah) { ?>
                      <span class="badge badge-ok">SELESAI</span>
                      <div class="muted mono" style="font-size:10px;margin-top:3px;">
                        <?= date('H:i', strtotime($selesai[(int)$h['id']]['completed_at'])) ?> WIB
                      </div>
                    <?php } else { ?>
                      <span class="badge badge-muted">BELUM</span>
                    <?php } ?>
                  </td>
                  <td>
                    <form method="post" action="<?= BASE_URL ?>log/toggle.php">
                      <input type="hidden" name="habit_id" value="<?= (int)$h['id'] ?>">
                      <input type="hidden" name="tanggal" value="<?= e($tanggal) ?>">
                      <?php if ($sudah) { ?>
                        <button class="btn-mini danger" type="submit">BATALKAN</button>
                      <?php } else { ?>
                        <button class="btn-mini ok" type="submit">CENTANG</button>
                      <?php } ?>
                    </form>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } ?>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// JEJAK_WAKTU</h2>
      <span class="panel-sub">URUT WAKTU</span>
    </div>

    <div class="panel-body">
      <?php if (count($selesai) === 0) { ?>
        <div class="empty-panel" style="border:none;padding:26px 8px;">
          <span class="empty-mark">▤</span>
          <h3>Belum ada centang</h3>
          <p>Belum ada protokol yang dicentang pada tanggal ini.</p>
        </div>
      <?php } else { ?>
        <div class="kv-list">
          <?php foreach ($selesai as $log) { ?>
            <div class="kv-row">
              <span class="kv-key" style="text-transform:none;letter-spacing:0;">
                <?= e($log['emblem']) ?> <?= e($log['nama_habit']) ?>
              </span>
              <span class="kv-val mono" style="font-size:11px;">
                <?= date('H:i:s', strtotime($log['completed_at'])) ?>
              </span>
            </div>
          <?php } ?>
        </div>

        <p class="form-hint" style="margin-top:14px;">
          Total <?= count($selesai) ?> protokol dicentang pada tanggal ini.
        </p>
      <?php } ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
