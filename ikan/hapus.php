<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('warn', 'Penghapusan harus lewat tombol HAPUS pada daftar ikan.');
    redirect(BASE_URL . 'ikan/index.php');
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'ID spesimen tidak valid.');
    redirect(BASE_URL . 'ikan/index.php');
}

if (!$db_connected) {
    set_flash('warn', 'MySQL belum aktif, penghapusan tidak dijalankan (mode demo).');
    redirect(BASE_URL . 'ikan/index.php');
}

$ikan = db_row("SELECT nama FROM fishes WHERE id = ? AND user_id = ?", 'ii', [$id, $user['id']]);

if (!$ikan) {
    set_flash('danger', 'Spesimen tidak ditemukan atau bukan milik akun ini.');
    redirect(BASE_URL . 'ikan/index.php');
}

$stmt = $conn->prepare("DELETE FROM fishes WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $id, $user['id']);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    set_flash('ok', 'Spesimen "' . $ikan['nama'] . '" berhasil dihapus.');
} else {
    set_flash('danger', 'Spesimen gagal dihapus. Coba lagi.');
}

redirect(BASE_URL . 'ikan/index.php');
