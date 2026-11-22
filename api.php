<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Sesi berakhir. Silakan login kembali.'
    ]);
    exit;
}

$action = isset($_GET['action']) ? (string)$_GET['action'] : '';

if (!$db_connected) {
    echo json_encode([
        'status'  => 'offline',
        'message' => 'Database belum aktif. Data sementara disimpan di browser.'
    ]);
    exit;
}

$user_id = (int)($_SESSION['user_id'] ?? 0);

if ($action === 'get_habits') {
    $tanggal = isset($_GET['tanggal']) ? (string)$_GET['tanggal'] : date('Y-m-d');

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $tanggal = date('Y-m-d');
    }

    $habits = db_all(
        "SELECT id, nama_habit, kategori, waktu, emblem, target_harian
         FROM habits WHERE user_id = ? ORDER BY id ASC",
        'i',
        [$user_id]
    );

    $logs = db_all(
        "SELECT l.habit_id
         FROM habit_logs l
         JOIN habits h ON h.id = l.habit_id
         WHERE h.user_id = ? AND l.tanggal = ? AND l.status = 1",
        'is',
        [$user_id, $tanggal]
    );

    $completed_ids = array_map(fn($row) => (int)$row['habit_id'], $logs);

    echo json_encode([
        'status'        => 'success',
        'habits'        => $habits,
        'completed_ids' => $completed_ids,
        'tanggal'       => $tanggal,
    ]);
    exit;
}

if ($action === 'create_habit') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];

    $nama     = trim((string)($data['nama'] ?? ''));
    $kategori = trim((string)($data['kategori'] ?? 'PAKAN'));
    $waktu    = trim((string)($data['waktu'] ?? 'Rutin Harian'));
    $emblem   = trim((string)($data['emblem'] ?? '🐟'));

    if ($nama === '') {
        echo json_encode(['status' => 'error', 'message' => 'Nama kebiasaan tidak boleh kosong.']);
        exit;
    }

    if (mb_strlen($nama) > 100) {
        echo json_encode(['status' => 'error', 'message' => 'Nama kebiasaan maksimal 100 karakter.']);
        exit;
    }

    $id = db_insert(
        "INSERT INTO habits (user_id, nama_habit, kategori, waktu, emblem) VALUES (?, ?, ?, ?, ?)",
        [$user_id, $nama, $kategori, $waktu === '' ? 'Rutin Harian' : $waktu, $emblem]
    );

    if ($id > 0) {
        echo json_encode(['status' => 'success', 'id' => $id, 'message' => 'Habit berhasil ditambahkan.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Habit gagal disimpan. Coba lagi.']);
    }
    exit;
}

if ($action === 'update_habit') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];

    $id       = (int)($data['id'] ?? 0);
    $nama     = trim((string)($data['nama'] ?? ''));
    $kategori = trim((string)($data['kategori'] ?? 'PAKAN'));
    $waktu    = trim((string)($data['waktu'] ?? 'Rutin Harian'));
    $emblem   = trim((string)($data['emblem'] ?? '🐟'));

    if ($id <= 0 || $nama === '') {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak valid.']);
        exit;
    }

    $berhasil = db_exec(
        "UPDATE habits SET nama_habit = ?, kategori = ?, waktu = ?, emblem = ?
         WHERE id = ? AND user_id = ?",
        [$nama, $kategori, $waktu === '' ? 'Rutin Harian' : $waktu, $emblem, $id, $user_id]
    );

    if ($berhasil && db_affected() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Habit berhasil diperbarui.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Habit gagal diperbarui.']);
    }
    exit;
}

if ($action === 'delete_habit') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid.']);
        exit;
    }

    $berhasil = db_exec("DELETE FROM habits WHERE id = ? AND user_id = ?", [$id, $user_id]);

    if ($berhasil && db_affected() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Habit berhasil dihapus.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Habit gagal dihapus.']);
    }
    exit;
}

if ($action === 'toggle_log') {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];

    $habit_id = (int)($data['habit_id'] ?? 0);
    $tanggal  = (string)($data['tanggal'] ?? date('Y-m-d'));

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $tanggal = date('Y-m-d');
    }

    if ($habit_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Habit tidak valid.']);
        exit;
    }

    $milik_user = db_row(
        "SELECT id FROM habits WHERE id = ? AND user_id = ? LIMIT 1",
        'ii',
        [$habit_id, $user_id]
    );

    if (!$milik_user) {
        echo json_encode(['status' => 'error', 'message' => 'Habit tidak ditemukan pada akun ini.']);
        exit;
    }

    $log = db_row(
        "SELECT id FROM habit_logs WHERE habit_id = ? AND tanggal = ? LIMIT 1",
        'is',
        [$habit_id, $tanggal]
    );

    if ($log) {
        db_exec("DELETE FROM habit_logs WHERE id = ?", [(int)$log['id']]);
        echo json_encode(['status' => 'success', 'action' => 'unchecked']);
    } else {
        db_exec(
            "INSERT INTO habit_logs (habit_id, tanggal, status) VALUES (?, ?, 1)",
            [$habit_id, $tanggal]
        );
        echo json_encode(['status' => 'success', 'action' => 'checked']);
    }
    exit;
}

if ($action === 'get_water_params') {
    $params = ambil_water_params_terbaru($user_id);

    if (!$params) {
        echo json_encode([
            'status'  => 'empty',
            'message' => 'Belum ada data parameter air.'
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'params' => $params + ['status_air' => status_parameter_air($params)],
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali.']);
