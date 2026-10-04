<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('CIPHERLAB_DB_HOST') ?: 'localhost';
    $port = getenv('CIPHERLAB_DB_PORT') ?: '5432';
    $database = getenv('CIPHERLAB_DB_NAME') ?: 'lab_login';
    $username = getenv('CIPHERLAB_DB_USER') ?: 'postgres';
    $password = getenv('CIPHERLAB_DB_PASSWORD') ?: 'krys2002';
    $pdo = new PDO(
        "pgsql:host={$host};port={$port};dbname={$database}",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    return $pdo;
}
