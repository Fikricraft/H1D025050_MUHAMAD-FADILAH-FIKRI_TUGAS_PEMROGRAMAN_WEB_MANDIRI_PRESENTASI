<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('warn', 'Penghapusan harus lewat tombol HAPUS.');
    redirect(BASE_URL . 'habit/index.php');
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'ID habit tidak valid.');
    redirect(BASE_URL . 'habit/index.php');
}

if (!$db_connected) {
    set_flash('warn', 'MySQL belum aktif, penghapusan tidak dijalankan (mode demo).');
    redirect(BASE_URL . 'habit/index.php');
}

$habit = db_row("SELECT nama_habit FROM habits WHERE id = ? AND user_id = ?", 'ii', [$id, $user['id']]);

if (!$habit) {
    set_flash('danger', 'Habit tidak ditemukan atau bukan milik akun ini.');
    redirect(BASE_URL . 'habit/index.php');
}

$stmt = $conn->prepare("DELETE FROM habits WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $id, $user['id']);

if ($stmt->execute()) {
    set_flash('ok', 'Habit "' . $habit['nama_habit'] . '" berhasil dihapus.');
} else {
    set_flash('danger', 'Habit gagal dihapus. Coba lagi.');
}

redirect(BASE_URL . 'habit/index.php');
