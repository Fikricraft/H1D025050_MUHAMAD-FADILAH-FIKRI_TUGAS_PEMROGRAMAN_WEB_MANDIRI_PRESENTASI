<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . 'log/index.php');
}

$habit_id = (int)($_POST['habit_id'] ?? 0);
$tanggal  = $_POST['tanggal'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    $tanggal = date('Y-m-d');
}

if ($habit_id <= 0) {
    set_flash('danger', 'Habit tidak valid.');
    redirect(BASE_URL . 'log/index.php?tanggal=' . urlencode($tanggal));
}

if (!$db_connected) {
    set_flash('warn', 'MySQL belum aktif, centang tidak bisa disimpan (mode demo).');
    redirect(BASE_URL . 'log/index.php?tanggal=' . urlencode($tanggal));
}

$habit = db_row("SELECT nama_habit FROM habits WHERE id = ? AND user_id = ?", 'ii', [$habit_id, $user['id']]);

if (!$habit) {
    set_flash('danger', 'Habit tidak ditemukan atau bukan milik akun ini.');
    redirect(BASE_URL . 'log/index.php?tanggal=' . urlencode($tanggal));
}

$log = db_row(
    "SELECT id FROM habit_logs WHERE habit_id = ? AND tanggal = ?",
    'is',
    [$habit_id, $tanggal]
);

if ($log) {
    $stmt = $conn->prepare("DELETE FROM habit_logs WHERE id = ?");
    $stmt->bind_param('i', $log['id']);
    $stmt->execute();

    set_flash('warn', 'Centang "' . $habit['nama_habit'] . '" dibatalkan.');
} else {
    $stmt = $conn->prepare("INSERT INTO habit_logs (habit_id, tanggal, status) VALUES (?, ?, 1)");
    $stmt->bind_param('is', $habit_id, $tanggal);
    $stmt->execute();

    set_flash('ok', 'Habit "' . $habit['nama_habit'] . '" dicentang untuk ' . tanggal_indonesia($tanggal) . '.');
}

redirect(BASE_URL . 'log/index.php?tanggal=' . urlencode($tanggal));
