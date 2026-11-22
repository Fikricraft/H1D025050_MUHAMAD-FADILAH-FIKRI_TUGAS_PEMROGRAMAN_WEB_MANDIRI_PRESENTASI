<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('warn', 'Penghapusan harus lewat tombol HAPUS pada daftar pakan.');
    redirect(BASE_URL . 'pakan/index.php');
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'Catatan pakan tidak valid.');
    redirect(BASE_URL . 'pakan/index.php');
}

if (!$db_connected) {
    set_flash('warn', 'Database belum aktif, penghapusan tidak dijalankan.');
    redirect(BASE_URL . 'pakan/index.php');
}

$feeding = ambil_feeding($id, (int)$user['id']);

if (!$feeding) {
    set_flash('danger', 'Catatan pakan tidak ditemukan atau bukan milik akun ini.');
    redirect(BASE_URL . 'pakan/index.php');
}

$berhasil = db_exec("DELETE FROM feedings WHERE id = ? AND user_id = ?", [$id, (int)$user['id']]);

if ($berhasil && db_affected() > 0) {
    set_flash('ok', 'Catatan pakan tanggal ' . tanggal_indonesia($feeding['tanggal']) . ' berhasil dihapus.');
} else {
    set_flash('danger', 'Catatan pakan gagal dihapus. Coba lagi.');
}

redirect(BASE_URL . 'pakan/index.php');
