<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/koneksi.php';

function demo_users(): array
{
    return [
        [
            'id'         => 1,
            'username'   => 'admin',
            'password'   => 'password123',
            'nama_ikan'  => 'Alpha Striker',
            'jenis_ikan' => 'Channa Marulioides',
        ],
    ];
}

function find_user(string $username): ?array
{
    global $db_connected;

    if ($db_connected) {
        return db_row(
            "SELECT id, username, password, nama_ikan, jenis_ikan
             FROM users WHERE username = ? LIMIT 1",
            's',
            [$username]
        );
    }

    foreach (demo_users() as $u) {
        if (strcasecmp($u['username'], $username) === 0) {
            return $u;
        }
    }

    return null;
}

function check_password(string $input, string $tersimpan): bool
{
    if (str_starts_with($tersimpan, '$2y$') || str_starts_with($tersimpan, '$argon')) {
        return password_verify($input, $tersimpan);
    }

    return hash_equals($tersimpan, $input);
}

function attempt_login(string $username, string $password): ?string
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return 'Username dan password wajib diisi.';
    }

    $user = find_user($username);

if (!$user || !check_password($password, $user['password'])) {
        return 'Username atau password salah.';
    }

    login_user($user);

    return null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);

    $_SESSION['user_id']    = (int)$user['id'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['nama_ikan']  = $user['nama_ikan'];
    $_SESSION['jenis_ikan'] = $user['jenis_ikan'];
    $_SESSION['login_at']   = time();
}

function current_user(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id'         => (int)$_SESSION['user_id'],
        'username'   => $_SESSION['username']    ?? 'Admin',
        'nama_ikan'  => $_SESSION['nama_ikan']   ?? 'Ikan Tanpa Nama',
        'jenis_ikan' => $_SESSION['jenis_ikan']  ?? 'Spesies tidak diketahui',
    ];
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (is_logged_in()) {
        return;
    }

    $tujuan = $_SERVER['REQUEST_URI'] ?? BASE_URL . 'index.php';
    header('Location: ' . BASE_URL . 'login.php?next=' . urlencode($tujuan));
    exit;
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
    forget_remember_cookie();
}

function remember_token(string $username): string
{
    return hash_hmac('sha256', $username, APP_SECRET);
}

function set_remember_cookie(string $username): void
{
    setcookie(
        'ikan_remember',
        $username . '|' . remember_token($username),
        time() + 60 * 60 * 24 * 30,   
        '/',
        '',
        false,
        true
    );
}

function forget_remember_cookie(): void
{
    setcookie('ikan_remember', '', time() - 3600, '/', '', false, true);
}

function restore_from_remember_cookie(): void
{
    if (is_logged_in() || !isset($_COOKIE['ikan_remember'])) {
        return;
    }

    $bagian = explode('|', $_COOKIE['ikan_remember']);

    if (count($bagian) !== 2) {
        forget_remember_cookie();
        return;
    }

    [$username, $token] = $bagian;

    if (!hash_equals(remember_token($username), $token)) {
        forget_remember_cookie();
        return;
    }

    $user = find_user($username);

    if ($user) {
        login_user($user);
    } else {
        forget_remember_cookie();
    }
}

function sinkron_user_database(): void
{
    global $db_connected;

    if (!$db_connected || !isset($_SESSION['user_id'])) {
        return;
    }

    $id  = (int)$_SESSION['user_id'];
    $row = db_row(
        "SELECT id, username, nama_ikan, jenis_ikan FROM users WHERE id = ? LIMIT 1",
        'i',
        [$id]
    );

    if (!$row) {
        $username = trim((string)($_SESSION['username'] ?? ''));

        if ($username !== '') {
            $row = db_row(
                "SELECT id, username, nama_ikan, jenis_ikan FROM users WHERE username = ? LIMIT 1",
                's',
                [$username]
            );
        }
    }

    if (!$row) {
        $username = trim((string)($_SESSION['username'] ?? ''));

        if ($username === '') {
            unset($_SESSION['user_id']);
            return;
        }

        $hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
        $baru = db_insert(
            "INSERT INTO users (username, password, nama_ikan, jenis_ikan) VALUES (?, ?, ?, ?)",
            [
                $username,
                $hash,
                (string)($_SESSION['nama_ikan'] ?? 'Ikan Baru'),
                (string)($_SESSION['jenis_ikan'] ?? 'Spesies belum diisi'),
            ]
        );

        if ($baru > 0) {
            $_SESSION['user_id'] = $baru;
        } else {
            return;
        }

        $row = db_row(
            "SELECT id, username, nama_ikan, jenis_ikan FROM users WHERE id = ? LIMIT 1",
            'i',
            [$baru]
        );
    }

    if ($row) {
        $_SESSION['user_id']    = (int)$row['id'];
        $_SESSION['username']   = $row['username'];
        $_SESSION['nama_ikan']  = $row['nama_ikan'];
        $_SESSION['jenis_ikan'] = $row['jenis_ikan'];
    }
}

restore_from_remember_cookie();
sinkron_user_database();

$theme_mode = $_COOKIE['ikan_theme'] ?? 'abyss-dark';
