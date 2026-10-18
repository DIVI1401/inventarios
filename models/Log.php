<?php
require_once __DIR__ . '/../config/Database.php';

class Log {
    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public static function registrar(int $idUsuario, string $accion, ?string $detalles = null): bool {
        try {
            $db = Database::conectar();
            $stmt = $db->prepare("INSERT INTO logs_actividad (idUsuario, accion, detalles) VALUES (:idUsuario, :accion, :detalles)");
            return $stmt->execute([
                ':idUsuario' => $idUsuario,
                ':accion'    => $accion,
                ':detalles'  => $detalles
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerTodos(): array {
        $sql = "SELECT l.*, u.nombre AS usuario, u.correo 
                FROM logs_actividad l 
                JOIN usuarios u ON l.idUsuario = u.idUsuario 
                ORDER BY l.fecha DESC LIMIT 100";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}