<?php

class Database {
    private static ?PDO $instance = null;

    private string $host     = 'localhost';
    private string $dbname   = 'ecoride';
    private string $username = 'root';
    private string $password = '';
    private string $charset  = 'utf8mb4';

    private function __construct() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $db = new self();
            $dsn = "mysql:host={$db->host};dbname={$db->dbname};charset={$db->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$instance = new PDO($dsn, $db->username, $db->password, $options);
            } catch (PDOException $e) {
                die(json_encode([
                    'success' => false,
                    'message' => 'Erreur de connexion à la base de données : ' . $e->getMessage()
                ]));
            }
        }
        return self::$instance;
    }
}
