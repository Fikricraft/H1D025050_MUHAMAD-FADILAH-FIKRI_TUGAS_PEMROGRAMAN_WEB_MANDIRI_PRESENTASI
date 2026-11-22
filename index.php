<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$jam = (int)date('H');
$salam = "SELAMAT DATANG";
if ($jam >= 5 && $jam < 11) {
    $salam = "SELAMAT PAGI // SESI MAKAN PERDANA";
} elseif ($jam >= 11 && $jam < 15) {
    $salam = "SELAMAT SIANG // CEK SIRIP & PARAMETER AIR";
} elseif ($jam >= 15 && $jam < 18) {
    $salam = "SELAMAT SORE // JADWAL PAKAN KEDUA";
} else {
    $salam = "SELAMAT MALAM // MODE REST AQUARIUM";
}

$nama_ikan = $_SESSION['nama_ikan'] ?? 'Alien Deepsea Specimen #09';
$jenis_ikan = $_SESSION['jenis_ikan'] ?? 'Abyssal Xenofish';
$username = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>IKAN// PROTOCOL - Fish Habit Tracking (Multi-Fish 3D Background)</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,600;0,700;1,700&family=JetBrains+Mono:wght@400;500;700&family=Teko:wght@600;700&display=swap" rel="stylesheet">
  <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="assets/css/pages.css" />
</head>
<body>

  <div class="grid-bg"></div>
  <div class="scanlines"></div>

  <div class="watermark-bg" aria-hidden="true">IKAN//TRACKER</div>

  <div class="bg-3d-fish-layer" aria-hidden="true">
    
    <div class="floating-3d-entity fish-alpha">
      <model-viewer
        src="assets/models/alien_fish_animated.glb"
        alt="Alien Fish Alpha"
        autoplay
        animation-name="*"
        orientation="0deg 0deg 90deg"
        shadow-intensity="0"
        exposure="1.25"
        disable-zoom
        disable-pan
        disable-tap
        interaction-prompt="none"
        loading="eager"
      ></model-viewer>
    </div>

    <div class="floating-3d-entity fish-beta">
      <model-viewer
        src="assets/models/alien_fish_animated.glb"
        alt="Alien Fish Beta"
        autoplay
        animation-name="*"
        orientation="0deg 0deg 90deg"
        shadow-intensity="0"
        exposure="1.0"
        disable-zoom
        disable-pan
        disable-tap
        interaction-prompt="none"
        loading="eager"
      ></model-viewer>
    </div>

    <div class="floating-3d-entity fish-gamma">
      <model-viewer
        src="assets/models/alien_fish_animated.glb"
        alt="Alien Fish Gamma"
        autoplay
        animation-name="*"
        orientation="0deg 0deg 90deg"
        shadow-intensity="0"
        exposure="0.75"
        disable-zoom
        disable-pan
        disable-tap
        interaction-prompt="none"
        loading="eager"
      ></model-viewer>
    </div>

  </div>

  <div class="container">
    
    <header class="top-nav">
      <div class="brand-tag">
        <span class="brand-main">IKAN<span class="brand-accent">0//.</span></span>
      </div>

      <div class="nav-badges">
        <div class="badge-status">
          <span class="status-indicator"></span>
          <span class="status-label">PHP SESSION :</span>
          <span class="status-val">[ <?= htmlspecialchars($username) ?> ]</span>
        </div>
        <div class="badge-status open-tag">
          <span class="dot-lime"></span>
          <span class="status-highlight">3D SQUAD: 3x ALIEN FISH SPECIMENS</span>
        </div>
        <div class="badge-status db-indicator <?= $db_connected ? 'db-online' : 'db-offline' ?>">
          <span>DB: <?= $db_connected ? 'MYSQL CONNECTED [ONLINE]' : 'STANDALONE [LOCAL STORAGE]' ?></span>
        </div>
      </div>
    </header>

    <nav class="dash-nav">
      <a class="dash-nav-link is-current" href="index.php">DASHBOARD</a>
      <a class="dash-nav-link" href="ikan/index.php">DATA IKAN</a>
      <a class="dash-nav-link" href="habit/index.php">HABIT</a>
      <a class="dash-nav-link" href="log/index.php">RIWAYAT</a>
      <a class="dash-nav-link" href="air/index.php">PARAMETER AIR</a>
      <a class="dash-nav-link" href="pakan/index.php">PAKAN</a>
      <a class="dash-nav-link" href="laporan.php">LAPORAN</a>
      <a class="dash-nav-link" href="profil.php">PROFIL</a>
      <a class="dash-nav-link logout" href="logout.php"
         onclick="return confirm('Keluar dari sistem?');">LOGOUT</a>
    </nav>

    <section class="hero-section">
      <h1 class="hero-title">
        <span class="row-1">PANTAU HABIT</span>
        <span class="row-2"><span class="highlight-box">IKAN</span> LU</span>
        <span class="row-3">DISIPLIN ATAU MATI<span class="underline-lime">.</span></span>
      </h1>

      <div class="hero-desc-box">
        <div class="tape-strip top-left"></div>
        <div class="tape-strip top-right"></div>
        <p>
          <span class="tag-inline"><?= $salam ?></span> &mdash; 
          Aplikasi tracking disiplin pakan, parameter air, dan sanitasi harian untuk <strong><?= htmlspecialchars($nama_ikan) ?></strong>.
          <span class="text-lime">Eksekusi checklist sekarang atau spesimen mati!</span>
        </p>
      </div>

      <div class="hero-action-row">
        <button class="btn-brutal" id="btnOpenAddModal">
          <span class="btn-text">TAMBAH LOG / HABIT BARU</span>
          <span class="btn-subtext">// RECORD HABIT PARAMETER</span>
          <span class="btn-arrow">→</span>
        </button>

        <div class="quick-status-pill">
          <span class="pill-dot"></span>
          <span class="pill-label">ENGINE: <strong>NATIVE PHP + MYSQL + DOM JS + MULTI-FISH 3D</strong></span>
        </div>
      </div>
    </section>

    <section class="control-panel">
      <div class="panel-header">
        <span class="terminal-text">// LOG_TIMESTAMP_CONTROLLER [DOM ALGORITMA PEMILIHAN]</span>
        <div class="date-controls">
          <button class="nav-btn" id="prevDayBtn" title="Hari Sebelumnya">&lt; PREV</button>
          <div class="current-date-badge" id="currentDateBadge">
            <span class="cal-icon">🗓</span>
            <span id="dateDisplay"><?= date('l, d M Y') ?></span>
          </div>
          <button class="nav-btn" id="nextDayBtn" title="Hari Berikutnya">NEXT &gt;</button>
          <button class="nav-btn today-btn" id="todayBtn">TODAY</button>
        </div>
      </div>

      <div class="progress-container">
        <div class="progress-info">
          <span class="progress-title">[ DAILY PROTOCOL COMPLETION ]</span>
          <span class="progress-stat"><strong id="progressPercent">0%</strong> (<span id="progressRatio">0/0</span> HABIT)</span>
        </div>
        <div class="meter-track">
          <div class="meter-bar" id="meterBar"></div>
        </div>
      </div>
    </section>

    <section class="tracker-layout">
      
      <div class="habits-column">
        <div class="column-header">
          <span class="col-title">// ACTIVE_HABITS_LIST [CRUD CONTROLLER]</span>
          <span class="col-count" id="habitCountBadge">MEMUAT PROTOKOL...</span>
        </div>

        <div class="habit-items-container" id="habitList">
        </div>

        <div class="empty-state" id="emptyState">
          <div class="empty-icon">⚠️</div>
          <div class="empty-title">TIDAK ADA DATA HABIT // LIST KOSONG</div>
          <p>Klik tombol <strong>"TAMBAH LOG / HABIT BARU"</strong> untuk memasukkan tugas harian ikan.</p>
        </div>
      </div>

      <div class="stats-column">
        
        <div class="brutal-card stat-card">
          <div class="card-tag">// METRIC_01 [ALGORITMA PERULANGAN]</div>
          <div class="stat-value" id="streakVal">0</div>
          <div class="stat-meta">HARI BERTURUT-TURUT</div>
          <div class="stat-footnote">&gt; STREAK DISIPLIN AKTIF</div>
        </div>

        <div class="brutal-card stat-card">
          <div class="card-tag">// METRIC_02 [HISTORI LOG]</div>
          <div class="stat-value" id="completedTotalVal">0</div>
          <div class="stat-meta">TOTAL CHECKLIST SELESAI</div>
          <div class="stat-footnote">&gt; HISTORI SELURUH LOG</div>
        </div>

        <div class="brutal-card stat-card">
          <div class="card-tag">// METRIC_03 [KESEHATAN IKAN]</div>
          <div class="stat-value" id="efficiencyVal">100%</div>
          <div class="stat-meta">STATUS KONDISI IKAN</div>
          <div class="stat-footnote" id="healthStatusFootnote">&gt; STATUS : EXCELLENT</div>
        </div>

        <div class="brutal-card stat-card">
          <div class="card-tag">// METRIC_04 [PARAMETER AIR TERBARU]</div>
          <div class="stat-value" id="waterPhVal" style="font-size:34px;">--</div>
          <div class="stat-meta">pH &amp; SUHU AIR</div>
          <div class="stat-footnote" id="waterStatusFootnote">&gt; BELUM ADA DATA AIR</div>
        </div>

        <div class="brutal-card weekly-card">
          <div class="card-tag">// WEEKLY_OVERVIEW [7 HARI PERULANGAN FOR]</div>
          <div class="weekly-grid" id="weeklyGrid">
          </div>
        </div>

      </div>
    </section>

    <footer class="bottom-bar">
      <div class="bottom-left">
        <span class="bullet">■</span>
        <span>IKAN0// HABIT TRACKER PROTOCOL &copy; <?= date('Y') ?>. PEMROGRAMAN WEB MANDIRI PRESENTASI.</span>
      </div>
      <div class="bottom-right">
        <span>[ 3D ALIEN FISH SQUAD ACTIVE IN BACKGROUND ]</span>
      </div>
    </footer>

  </div>

  <div class="modal-overlay" id="crudModal">
    <div class="modal-box">
      <div class="modal-top">
        <span class="modal-title" id="modalTitle">[ ENTRY : BARU ]</span>
        <button class="modal-close" id="btnCloseModal">✕</button>
      </div>

      <form id="habitForm" class="modal-form">
        <input type="hidden" id="editHabitId" value="" />

        <div class="form-group">
          <label for="habitTitleInput">// NAMA PROTOKOL / HABIT</label>
          <input 
            type="text" 
            id="habitTitleInput" 
            placeholder="Contoh: Beri Pakan Pelet Pagi..." 
            required 
            maxlength="60"
            autocomplete="off"
          />
        </div>

        <div class="form-group">
          <label for="habitCategoryInput">// KATEGORI OPERASIONAL</label>
          <select id="habitCategoryInput">
            <option value="PAKAN">PAKAN / NUTRISI</option>
            <option value="KUALITAS AIR">KUALITAS AIR / WATER CHANGE</option>
            <option value="SANITASI">SANITASI / FILTER & AQUARIUM</option>
            <option value="KESEHATAN">KESEHATAN / CEK SIRIP & FISIK</option>
            <option value="SUHU & LAMPU">SUHU, OKSIGEN & PENCAHAYAAN</option>
          </select>
        </div>

        <div class="form-group">
          <label for="habitTimeInput">// JADWAL / WAKTU EKSEKUSI</label>
          <input 
            type="text" 
            id="habitTimeInput" 
            placeholder="Contoh: 07:30 WIB / Rutin" 
            maxlength="30"
          />
        </div>

        <div class="form-group">
          <label>// PILIH KODE EMBLEM IKAN</label>
          <div class="emoji-selector" id="emojiSelector">
            <button type="button" class="emoji-btn selected" data-emoji="🐟">🐟</button>
            <button type="button" class="emoji-btn" data-emoji="🐠">🐠</button>
            <button type="button" class="emoji-btn" data-emoji="🐡">🐡</button>
            <button type="button" class="emoji-btn" data-emoji="🦈">🦈</button>
            <button type="button" class="emoji-btn" data-emoji="💧">💧</button>
            <button type="button" class="emoji-btn" data-emoji="🌡️">🌡️</button>
            <button type="button" class="emoji-btn" data-emoji="🧽">🧽</button>
            <button type="button" class="emoji-btn" data-emoji="💊">💊</button>
            <button type="button" class="emoji-btn" data-emoji="⏱️">⏱️</button>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn-secondary" id="btnCancelModal">BATAL</button>
          <button type="submit" class="btn-primary" id="btnSaveHabit">SIMPAN DATA //</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="deleteConfirmModal">
    <div class="modal-box delete-box">
      <div class="modal-top danger-top">
        <span class="modal-title">[ PERINGATAN // HAPUS DATA ]</span>
        <button class="modal-close" id="btnCancelDelete">✕</button>
      </div>
      <div class="modal-body-danger">
        <div class="danger-icon">⚠️</div>
        <p class="danger-text" id="deleteMsg">Apakah Anda yakin ingin menghapus kebiasaan ini secara permanen?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" id="btnAbortDelete">BATAL</button>
        <button type="button" class="btn-danger" id="btnExecuteDelete">YA, HAPUS //</button>
      </div>
    </div>
  </div>

  <div class="toast-container" id="toastContainer"></div>

  <script src="assets/js/script.js"></script>
  <script>
    
  fetch('api.php?action=get_water_params')
    .then(function (r) { return r.json(); })
    .then(function (data) {
      var phEl = document.getElementById('waterPhVal');
      var foot = document.getElementById('waterStatusFootnote');
      if (phEl && foot && data && data.status === 'success' && data.params) {
        phEl.textContent = data.params.ph + ' pH';
        var statusAir = data.params.status_air || {};
        var jumlahAman = Object.keys(statusAir).filter(function (k) {
          return statusAir[k] === 'AMAN';
        }).length;
        foot.textContent = '> ' + jumlahAman + ' DARI 5 PARAMETER AMAN';
      } else if (phEl && foot) {
        phEl.textContent = '--';
        foot.textContent = '> BELUM ADA DATA AIR';
      }
    })
    .catch(function () {
      var phEl = document.getElementById('waterPhVal');
      var foot = document.getElementById('waterStatusFootnote');
      if (phEl && foot) {
        phEl.textContent = '--';
        foot.textContent = '> MODE DEMO / DB MATI';
      }
    });
  </script>
</body>
</html>
