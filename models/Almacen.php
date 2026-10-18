<?php

require_once __DIR__ . '/../config/Database.php';

class Almacen {
    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function listarTodos(): array {
        $stmt = $this->db->query("SELECT * FROM almacenes ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM almacenes WHERE idAlmacen = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function crear(string $nombre, string $ubicacion, string $descripcion): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO almacenes (nombre, ubicacion, descripcion) 
             VALUES (:nombre, :ubicacion, :descripcion)"
        );
        return $stmt->execute([
            'nombre' => $nombre,
            'ubicacion' => $ubicacion,
            'descripcion' => $descripcion,
        ]);
    }

    public function editar(int $id, string $nombre, string $ubicacion, string $descripcion): bool {
        $stmt = $this->db->prepare(
            "UPDATE almacenes SET nombre = :nombre, ubicacion = :ubicacion, descripcion = :descripcion
             WHERE idAlmacen = :id"
        );
        return $stmt->execute([
            'nombre' => $nombre,
            'ubicacion' => $ubicacion,
            'descripcion' => $descripcion,
            'id' => $id,
        ]);
    }

    public function cambiarEstado(int $id, bool $estado): bool {
        $stmt = $this->db->prepare("UPDATE almacenes SET estado = :estado WHERE idAlmacen = :id");
        return $stmt->execute(['estado' => $estado, 'id' => $id]);
    }
}