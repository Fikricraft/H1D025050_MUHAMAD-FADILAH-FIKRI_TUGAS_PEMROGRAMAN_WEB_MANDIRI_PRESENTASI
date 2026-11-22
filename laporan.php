<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();

$rentang = (int)($_GET['rentang'] ?? 30);
if (!in_array($rentang, [7, 30, 90], true)) {
    $rentang = 30;
}

$habits = ambil_habits($user['id']);
$total_habit = count($habits);

$logs = [];
if ($db_connected) {
    $logs = db_all(
        "SELECT l.tanggal, l.habit_id, h.nama_habit, h.emblem, h.kategori
         FROM habit_logs l
         JOIN habits h ON h.id = l.habit_id
         WHERE h.user_id = ?
           AND l.tanggal >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
         ORDER BY l.tanggal ASC",
        'ii',
        [$user['id'], $rentang - 1]
    );
}

function rekap_per_tanggal(array $logs): array
{
    $hasil = [];

    foreach ($logs as $l) {
        $tgl = $l['tanggal'];
        $hasil[$tgl] = ($hasil[$tgl] ?? 0) + 1;
    }

    return $hasil;
}

function rekap_per_habit(array $logs): array
{
    $hasil = [];

    foreach ($logs as $l) {
        $id = (int)$l['habit_id'];

        if (!isset($hasil[$id])) {
            $hasil[$id] = [
                'nama'    => $l['nama_habit'],
                'emblem'  => $l['emblem'],
                'kategori'=> $l['kategori'],
                'total'   => 0,
            ];
        }

        $hasil[$id]['total']++;
    }

    uasort($hasil, fn($a, $b) => $b['total'] <=> $a['total']);

    return $hasil;
}

function hitung_streak(array $per_tanggal, int $total_habit): int
{
    if ($total_habit === 0) {
        return 0;
    }

    $streak = 0;

    for ($i = 0; $i < 365; $i++) {
        $tgl = date('Y-m-d', strtotime("-$i day"));
        $jumlah = $per_tanggal[$tgl] ?? 0;

        if ($jumlah >= $total_habit) {
            $streak++;
            continue;
        }

        if ($i === 0) {
            continue;
        }

        break;
    }

    return $streak;
}

$per_tanggal = rekap_per_tanggal($logs);
$per_habit   = rekap_per_habit($logs);

$total_centang   = count($logs);
$hari_ada_data   = max(1, $rentang);
$rata_per_hari   = $total_centang / $hari_ada_data;
$target_maksimal = $total_habit * $rentang;
$persen_keseluruhan = $target_maksimal > 0
    ? (int)round($total_centang / $target_maksimal * 100)
    : 0;

$hari_sempurna = 0;
foreach ($per_tanggal as $tgl => $jumlah) {
    if ($total_habit > 0 && $jumlah >= $total_habit) {
        $hari_sempurna++;
    }
}

$streak = hitung_streak($per_tanggal, $total_habit);

if (($_GET['format'] ?? '') === 'csv') {
    $nama_file = 'laporan_ikan_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nama_file . '"');

    $keluaran = fopen('php://output', 'w');

    fwrite($keluaran, "\xEF\xBB\xBF");

    fputcsv($keluaran, ['Laporan Checklist IKAN// PROTOCOL']);
    fputcsv($keluaran, ['User', $user['username']]);
    fputcsv($keluaran, ['Rentang', $rentang . ' hari terakhir']);
    fputcsv($keluaran, ['Dicetak', date('d-m-Y H:i')]);
    fputcsv($keluaran, []);

    fputcsv($keluaran, ['Tanggal', 'Jumlah Centang', 'Total Habit', 'Persentase']);

    foreach ($per_tanggal as $tgl => $jumlah) {
        $persen = $total_habit > 0 ? round($jumlah / $total_habit * 100) : 0;
        fputcsv($keluaran, [$tgl, $jumlah, $total_habit, $persen . '%']);
    }

    fputcsv($keluaran, []);
    fputcsv($keluaran, ['Habit', 'Kategori', 'Total Centang']);

    foreach ($per_habit as $h) {
        fputcsv($keluaran, [$h['nama'], $h['kategori'], $h['total']]);
    }

    fclose($keluaran);
    exit;
}

$page_title   = 'Laporan';
$page_eyebrow = 'ANALYTICS MODULE';
$page_desc    = 'Rekap kedisiplinan perawatan harian: total centang, hari sempurna, dan streak.';
$active       = 'laporan';

include __DIR__ . '/includes/header.php';
?>

<div class="tabs">
  <?php foreach ([7 => '7 HARI', 30 => '30 HARI', 90 => '90 HARI'] as $r => $label) { ?>
    <a class="tab <?= $rentang === $r ? 'is-active' : '' ?>"
       href="<?= BASE_URL ?>laporan.php?rentang=<?= $r ?>"><?= $label ?></a>
  <?php } ?>

  <a class="tab" href="<?= BASE_URL ?>laporan.php?rentang=<?= $rentang ?>&amp;format=csv"
     style="margin-left:auto;">⬇ UNDUH CSV</a>
</div>

<?php if (!$db_connected) { ?>
  <div class="flash flash-warn">
    <span class="flash-mark">▲</span>
    MySQL belum aktif. Laporan akan terisi setelah database dinyalakan dan
    checklist mulai dicentang.
  </div>
<?php } ?>

<div class="stat-grid">
  <div class="stat-box">
    <div class="stat-label">Total Centang</div>
    <div class="stat-number"><?= $total_centang ?></div>
    <div class="stat-note dim"><?= $rentang ?> HARI TERAKHIR</div>
  </div>

  <div class="stat-box">
    <div class="stat-label">Rata-rata / Hari</div>
    <div class="stat-number"><?= number_format($rata_per_hari, 1, ',', '.') ?></div>
    <div class="stat-note">DARI <?= $total_habit ?> HABIT</div>
  </div>

  <div class="stat-box">
    <div class="stat-label">Hari Sempurna</div>
    <div class="stat-number"><?= $hari_sempurna ?></div>
    <div class="stat-note">SEMUA HABIT DICENTANG</div>
  </div>

  <div class="stat-box">
    <div class="stat-label">Streak</div>
    <div class="stat-number"><?= $streak ?><small> hari</small></div>
    <div class="stat-note <?= $streak > 0 ? '' : 'dim' ?>">
      <?= $streak > 0 ? 'DISIPLIN TERJAGA' : 'BELUM ADA STREAK' ?>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <h2 class="panel-title">// PROGRES_KESELURUHAN</h2>
    <span class="panel-sub"><?= $persen_keseluruhan ?>% DARI TARGET <?= $target_maksimal ?> CENTANG</span>
  </div>
  <div class="panel-body">
    <div class="bar-track" style="height:14px;">
      <div class="bar-fill" style="width:<?= $persen_keseluruhan ?>%;"></div>
    </div>
    <div class="kv-list" style="margin-top:16px;">
      <div class="kv-row">
        <span class="kv-key">Target maksimal</span>
        <span class="kv-val mono"><?= $total_habit ?> habit &times; <?= $rentang ?> hari = <?= $target_maksimal ?></span>
      </div>
      <div class="kv-row">
        <span class="kv-key">Tercapai</span>
        <span class="kv-val mono"><?= $total_centang ?> centang (<?= $persen_keseluruhan ?>%)</span>
      </div>
      <div class="kv-row">
        <span class="kv-key">Hari dengan aktivitas</span>
        <span class="kv-val mono"><?= count($per_tanggal) ?> hari</span>
      </div>
    </div>
  </div>
</div>

<div class="split equal">
  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// PERINGKAT_HABIT</h2>
      <span class="panel-sub"><?= count($per_habit) ?> HABIT</span>
    </div>

    <div class="panel-body">
      <?php if (empty($per_habit)) { ?>
        <div class="empty-panel" style="border:none;padding:26px 8px;">
          <span class="empty-mark">▦</span>
          <h3>Belum ada data</h3>
          <p>Centang habit di dashboard atau halaman Riwayat.</p>
        </div>
      <?php } else { ?>
        <?php
          $tertinggi = max(array_map(fn($h) => $h['total'], $per_habit));
          foreach ($per_habit as $h) {
              $lebar = (int)round($h['total'] / max(1, $tertinggi) * 100);
        ?>
          <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;gap:10px;font-size:12.5px;margin-bottom:5px;">
              <span><?= e($h['emblem']) ?> <?= e($h['nama']) ?>
                <span class="badge badge-info" style="margin-left:6px;"><?= e($h['kategori']) ?></span>
              </span>
              <span class="mono" style="color:var(--brand-cyan);white-space:nowrap;">
                <?= $h['total'] ?>x
              </span>
            </div>
            <div class="bar-track" style="height:6px;">
              <div class="bar-fill" style="width:<?= max(3, $lebar) ?>%;"></div>
            </div>
          </div>
        <?php } ?>
      <?php } ?>
    </div>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h2 class="panel-title">// MATRIKS_7_HARI</h2>
      <span class="panel-sub">JUMLAH CENTANG / HARI</span>
    </div>

    <div class="panel-body">
      <div class="table-wrap">
        <table class="table-cyber" style="min-width:0;">
          <thead>
            <tr><th>Tanggal</th><th>Hari</th><th class="num">Centang</th><th class="num">%</th></tr>
          </thead>
          <tbody>
            <?php for ($i = 6; $i >= 0; $i--) {
                $tgl    = date('Y-m-d', strtotime("-$i day"));
                $jumlah = $per_tanggal[$tgl] ?? 0;
                $persen = $total_habit > 0 ? (int)round($jumlah / $total_habit * 100) : 0;
            ?>
              <tr>
                <td class="mono"><?= date('d/m', strtotime($tgl)) ?></td>
                <td class="muted"><?= nama_hari($tgl) ?></td>
                <td class="num"><?= $jumlah ?> / <?= $total_habit ?></td>
                <td class="num">
                  <span class="badge <?= $persen >= 100 ? 'badge-ok' : ($persen > 0 ? 'badge-warn' : 'badge-muted') ?>">
                    <?= $persen ?>%
                  </span>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <p class="form-hint" style="margin-top:14px;">
        Persentase 100% berarti seluruh habit pada hari itu sudah dicentang.
      </p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
