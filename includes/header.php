<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

require_login();

$user         = current_user();
$page_title   = $page_title   ?? APP_NAME;
$active       = $active       ?? '';
$page_eyebrow = $page_eyebrow ?? 'IKAN// PROTOCOL';
$page_desc    = $page_desc    ?? '';
$flash        = take_flash();

$menu = [
    'dashboard' => ['label' => 'DASHBOARD',  'url' => 'index.php',        'icon' => '◉'],
    'ikan'      => ['label' => 'DATA IKAN',  'url' => 'ikan/index.php',   'icon' => '🐟'],
    'habit'     => ['label' => 'HABIT',      'url' => 'habit/index.php',  'icon' => '☑'],
    'log'       => ['label' => 'RIWAYAT',    'url' => 'log/index.php',    'icon' => '▤'],
    'air'       => ['label' => 'AIR',        'url' => 'air/index.php',    'icon' => '💧'],
    'pakan'     => ['label' => 'PAKAN',      'url' => 'pakan/index.php',  'icon' => '🍽'],
    'laporan'   => ['label' => 'LAPORAN',    'url' => 'laporan.php',      'icon' => '▦'],
    'profil'    => ['label' => 'PROFIL',     'url' => 'profil.php',       'icon' => '⚙'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title) ?> &middot; <?= APP_NAME ?></title>

  <link rel="icon" href="<?= BASE_URL ?>assets/img/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@400;600;700&family=JetBrains+Mono:wght@400;500;700&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/pages.css">
</head>
<body class="page-inner <?= $theme_mode === 'mint-cyan' ? 'theme-mint' : '' ?>">

  <div class="grid-bg"></div>
  <div class="scanlines"></div>

  <header class="shell-topbar">
    <div class="shell-wrap">
      <a class="shell-brand" href="<?= BASE_URL ?>index.php">
        IKAN<span class="accent">0//.</span>
      </a>

      <nav class="shell-nav">
        <?php foreach ($menu as $kunci => $item) { ?>
          <a href="<?= BASE_URL . $item['url'] ?>"
             class="shell-nav-link <?= $active === $kunci ? 'is-active' : '' ?>">
            <span class="nav-icon"><?= $item['icon'] ?></span>
            <?= $item['label'] ?>
          </a>
        <?php } ?>
      </nav>

      <div class="shell-user">
        <span class="db-dot <?= $db_connected ? 'on' : 'off' ?>"
              title="<?= $db_connected ? 'MySQL terhubung' : 'MySQL mati - mode demo' ?>"></span>
        <span class="user-name"><?= e($user['username']) ?></span>
        <a class="btn-mini danger" href="<?= BASE_URL ?>logout.php"
           onclick="return confirm('Keluar dari sistem?');">LOGOUT</a>
      </div>
    </div>
  </header>

  <main class="shell-wrap shell-main">

    <div class="page-head">
      <div>
        <span class="eyebrow">// <?= e($page_eyebrow) ?></span>
        <h1 class="page-title"><?= e($page_title) ?></h1>
        <?php if ($page_desc !== '') { ?>
          <p class="page-desc"><?= e($page_desc) ?></p>
        <?php } ?>
      </div>

      <div class="page-head-meta">
        <span class="meta-chip"><?= e($user['nama_ikan']) ?></span>
        <span class="meta-chip dim"><?= e($user['jenis_ikan']) ?></span>
        <span class="meta-chip mono"><?= tanggal_indonesia(date('Y-m-d')) ?></span>
      </div>
    </div>

    <?php if ($flash) { ?>
      <div class="flash flash-<?= e($flash['tipe']) ?>">
        <span class="flash-mark">■</span>
        <?= e($flash['pesan']) ?>
      </div>
    <?php } ?>

    <?php if (!$db_connected) { ?>
      <div class="flash flash-warn">
        <span class="flash-mark">▲</span>
        Database belum aktif, aplikasi berjalan dalam <b>mode demo</b>.
        Nyalakan MySQL di XAMPP agar data bisa disimpan permanen.
      </div>
    <?php } ?>
