<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');                  
define('DB_NAME', 'fish_habit_tracker');

mysqli_report(MYSQLI_REPORT_OFF);

$conn             = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db_connected     = true;
$db_error_message = '';

if ($conn->connect_error) {
    $db_connected     = false;
    $db_error_message = $conn->connect_error;

    error_log('[IKAN] Koneksi database gagal: ' . $conn->connect_error);
} else {
    $conn->set_charset('utf8mb4');
}

function db_tipe($nilai): string
{
    if (is_int($nilai) || is_bool($nilai)) {
        return 'i';
    }

    if (is_float($nilai)) {
        return 'd';
    }

    return 's';
}

function db_jalankan(string $sql, array $params = [])
{
    global $conn, $db_connected;

    if (!$db_connected) {
        return false;
    }

    if (empty($params)) {
        $hasil = $conn->query($sql);

        if ($hasil === false) {
            error_log('[IKAN] Query gagal: ' . $conn->error . ' | ' . $sql);
        }

        return $hasil;
    }

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log('[IKAN] Prepare gagal: ' . $conn->error . ' | ' . $sql);

        return false;
    }

    $tipe = '';
    foreach ($params as $nilai) {
        $tipe .= db_tipe($nilai);
    }

    $stmt->bind_param($tipe, ...array_values($params));

    if (!$stmt->execute()) {
        error_log('[IKAN] Eksekusi gagal: ' . $stmt->error . ' | ' . $sql);

        return false;
    }

    $GLOBALS['db_stmt_terakhir'] = $stmt;

    if ($stmt->field_count > 0) {
        $hasil = $stmt->get_result();
        $stmt->close();

        return $hasil;
    }

    return $stmt;
}

function db_all(string $sql, string $types = '', array $params = []): array
{
    $hasil = db_jalankan($sql, $params);

    if (!$hasil instanceof mysqli_result) {
        return [];
    }

    $baris = [];

    while ($row = $hasil->fetch_assoc()) {
        $baris[] = $row;
    }

    return $baris;
}

function db_row(string $sql, string $types = '', array $params = []): ?array
{
    $hasil = db_jalankan($sql, $params);

    if (!$hasil instanceof mysqli_result) {
        return null;
    }

    $row = $hasil->fetch_assoc();
    $hasil->free();

    return $row ?: null;
}

function db_query(string $sql, string $types = '', array $params = [])
{
    return db_jalankan($sql, $params);
}

function db_exec(string $sql, array $params = []): bool
{
    return db_jalankan($sql, $params) !== false;
}

function db_insert(string $sql, array $params = []): int
{
    $stmt = db_jalankan($sql, $params);

    if (!$stmt instanceof mysqli_stmt) {
        return 0;
    }

    return (int)$stmt->insert_id;
}

function db_affected(): int
{
    $stmt = $GLOBALS['db_stmt_terakhir'] ?? null;

    if ($stmt instanceof mysqli_stmt) {
        return (int)$stmt->affected_rows;
    }

    global $conn;

    return (int)$conn->affected_rows;
}
