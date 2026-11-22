<?php

define('APP_NAME',    'IKAN// PROTOCOL');
define('APP_TAGLINE', 'Fish Habit Tracking System');
define('APP_VERSION', '2.1');

date_default_timezone_set('Asia/Jakarta');

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

define('APP_SECRET', 'ikan-protocol-secret-key-2026');

$script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));

foreach (['/ikan', '/habit', '/log', '/air', '/pakan'] as $sub) {
    if (str_ends_with($script_dir, $sub)) {
        $script_dir = substr($script_dir, 0, -strlen($sub));
    }
}
define('BASE_URL', rtrim($script_dir, '/') . '/');
