<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user   = current_user();
$habits = ambil_habits($user['id']);

$statistik = [];
if ($db_connected) {
    $rows = db_all(
        "SELECT h.id, COUNT(l.id) AS total
         FROM habits h
         LEFT JOIN habit_logs l
                ON l.habit_id = h.id
               AND l.tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         WHERE h.user_id = ?
         GROUP BY h.id",
        'i',
        [$user['id']]
    );

    foreach ($rows as $r) {
        $statistik[(int)$r['id']] = (int)$r['total'];
    }
}

$kategori = [];
foreach ($habits as $h) {
    $kategori[$h['kategori']] = ($kategori[$h['kategori']] ?? 0) + 1;
}

$page_title   = 'Habit Harian';
$page_eyebrow = 'PROTOCOL LIBRARY';
$page_desc    = 'Daftar protokol perawatan yang bisa dicentang setiap hari.';
$active       = 'habit';

include __DIR__ . '/../includes/header.php';
?>

<div class="stat-grid">
  <div class="stat-box">
    <div class="stat-label">Total Habit</div>
    <div class="stat-number"><?= count($habits) ?></div>
    <div class="stat-note">PROTOKOL AKTIF</div>
  </div>

  <div class="stat-box">
    <div class="stat-label">Kategori</div>
    <div class="stat-number"><?= count($kategori) ?></div>
    <div class="stat-note dim">
      <?= e(implode(' / ', array_keys($kategori))) ?>
    </div>
  </div>

  <div class="stat-box">
    <div class="stat-label">Total Centang 30 Hari</div>
    <div class="stat-number"><?= array_sum($statistik) ?></div>
    <div class="stat-note">30 HARI TERAKHIR</div>
  </div>
</div>

<div class="toolbar">
  <div class="toolbar-group">
    <input class="form-input" type="text" placeholder="Cari habit / kategori..."
           style="max-width:300px;" data-table-filter="#tabelHabit">
  </div>
  <a class="btn-cyber" href="<?= BASE_URL ?>habit/tambah.php">+ TAMBAH HABIT</a>
</div>

<div class="panel">
  <div class="panel-head">
    <h2 class="panel-title">// DAFTAR_HABIT</h2>
    <span class="panel-sub"><?= count($habits) ?> PROTOKOL</span>
  </div>

  <div class="panel-body tight">
    <?php if (count($habits) === 0) { ?>
      <div class="empty-panel" style="border:none;">
        <span class="empty-mark">☑</span>
        <h3>Belum ada habit</h3>
        <p>Tambahkan protokol pertama, misalnya "Beri pakan pagi".</p>
      </div>
    <?php } else { ?>
      <div class="table-wrap">
        <table class="table-cyber" id="tabelHabit">
          <thead>
            <tr>
              <th width="50">ID</th>
              <th width="46">-</th>
              <th>Nama Habit</th>
              <th>Kategori</th>
              <th>Jadwal</th>
              <th class="num">Target</th>
              <th class="num">30 Hari</th>
              <th width="160">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($habits as $h) { ?>
              <tr>
                <td class="mono muted">#<?= (int)$h['id'] ?></td>
                <td class="center" style="font-size:18px;"><?= e($h['emblem']) ?></td>
                <td><b><?= e($h['nama_habit']) ?></b></td>
                <td><span class="badge badge-info"><?= e($h['kategori']) ?></span></td>
                <td class="muted"><?= e($h['waktu']) ?></td>
                <td class="num"><?= (int)$h['target_harian'] ?>x</td>
                <td class="num"><?= $statistik[(int)$h['id']] ?? 0 ?>x</td>
                <td>
                  <div class="aksi">
                    <a class="btn-mini ok" href="<?= BASE_URL ?>habit/edit.php?id=<?= (int)$h['id'] ?>">EDIT</a>

                    <form method="post" action="<?= BASE_URL ?>habit/hapus.php" style="display:inline;">
                      <input type="hidden" name="id" value="<?= (int)$h['id'] ?>">
                      <button class="btn-mini danger" type="submit"
                              data-confirm="Hapus habit '<?= e($h['nama_habit']) ?>'? Riwayat centangnya juga terhapus.">
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
    <?php } ?>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <h2 class="panel-title">// CARA_KERJA</h2>
  </div>
  <div class="panel-body">
    <p class="page-desc" style="margin:0;">
      Halaman ini mengelola daftar protokol perawatan: nama, kategori, dan
      jadwal. Pencatatan centang harian dilakukan di dashboard atau di menu
      RIWAYAT. Satu protokol hanya bisa dicentang satu kali per hari.
    </p>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
