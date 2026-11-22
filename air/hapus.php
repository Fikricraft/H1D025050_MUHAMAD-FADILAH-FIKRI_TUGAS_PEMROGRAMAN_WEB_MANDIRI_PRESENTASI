<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('warn', 'Penghapusan harus lewat tombol HAPUS pada daftar parameter air.');
    redirect(BASE_URL . 'air/index.php');
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'Data parameter air tidak valid.');
    redirect(BASE_URL . 'air/index.php');
}

if (!$db_connected) {
    set_flash('warn', 'Database belum aktif, penghapusan tidak dijalankan.');
    redirect(BASE_URL . 'air/index.php');
}

$data_lama = ambil_water_param($id, (int)$user['id']);

if (!$data_lama) {
    set_flash('danger', 'Data parameter air tidak ditemukan atau bukan milik akun ini.');
    redirect(BASE_URL . 'air/index.php');
}

$berhasil = db_exec("DELETE FROM water_params WHERE id = ? AND user_id = ?", [$id, (int)$user['id']]);

if ($berhasil && db_affected() > 0) {
    set_flash('ok', 'Data parameter air ' . tanggal_indonesia($data_lama['tanggal']) . ' berhasil dihapus.');
} else {
    set_flash('danger', 'Data parameter air gagal dihapus. Coba lagi.');
}

redirect(BASE_URL . 'air/index.php');
