<?php
mysqli_report(MYSQLI_REPORT_OFF);

function connect_database(): mysqli
{
    $connection = new mysqli('localhost', 'root', '', 'login_db');

    if ($connection->connect_error) {
        throw new RuntimeException('Adatbázis-kapcsolati hiba');
    }

    $connection->set_charset('utf8mb4');
    return $connection;
}

function start_app_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
