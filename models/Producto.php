<?php

require_once __DIR__ . '/../config/Database.php';

class Producto {
    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function listarTodos(): array {
        $sql = "SELECT p.*, c.nombre AS categoria 
                FROM productos p 
                LEFT JOIN categorias c ON p.idCategoria = c.idCategoria 
                ORDER BY p.idProducto DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $idProducto): ?array {
        $sql = "SELECT * FROM productos WHERE idProducto = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idProducto]);
        return $stmt->fetch() ?: null;
    }

    public function crear(string $nombre, string $descripcion, float $precio, ?int $idCategoria): int {
        $sql = "INSERT INTO productos (nombre, descripcion, precio, idCategoria, estado) VALUES (?, ?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre, $descripcion, $precio, $idCategoria]);
        return (int)$this->db->lastInsertId();
    }

    public function actualizar(int $idProducto, string $nombre, string $descripcion, float $precio, ?int $idCategoria, int $estado): bool {
        $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, idCategoria = ?, estado = ? WHERE idProducto = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$nombre, $descripcion, $precio, $idCategoria, $estado, $idProducto]);
    }

    public function cambiarEstado(int $idProducto, int $estado): bool {
        $sql = "UPDATE productos SET estado = ? WHERE idProducto = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estado, $idProducto]);
    }
}