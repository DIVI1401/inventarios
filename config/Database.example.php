<?php

class Database {
    private static ?PDO $instancia = null;

    private function __construct() {}

    public static function conectar(): PDO {
        if (self::$instancia === null) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $puerto = getenv('DB_PORT') ?: '3306';
            $dbName = getenv('DB_NAME') ?: 'aetherstock';
            $usuario = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: '';

            $dsn = "mysql:host={$host};port={$puerto};dbname={$dbName};charset=utf8mb4";

            try {
                self::$instancia = new PDO($dsn, $usuario, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }

        return self::$instancia;
    }
}