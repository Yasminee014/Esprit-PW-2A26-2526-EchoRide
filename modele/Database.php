<?php
declare(strict_types=1);

final class Database
{
    private const DB_HOST = '127.0.0.1';
    private const DB_NAME = 'lostfound_front';
    private const DB_USER = 'root';
    private const DB_PASS = '';

    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', self::DB_HOST, self::DB_NAME);

        self::$pdo = new PDO(
            $dsn,
            self::DB_USER,
            self::DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return self::$pdo;
    }
}
