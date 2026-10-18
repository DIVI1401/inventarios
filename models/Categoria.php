<?php

require_once __DIR__ . '/../config/Database.php';

class Categoria {
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function listarTodas() {
        $stmt = $this->db->query("SELECT * FROM categorias ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE idCategoria = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $descripcion) {
        $stmt = $this->db->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
        return $stmt->execute([$nombre, $descripcion]);
    }

    public function editar($id, $nombre, $descripcion) {
        $stmt = $this->db->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE idCategoria = ?");
        return $stmt->execute([$nombre, $descripcion, $id]);
    }

    public function cambiarEstado($id, $estado) {
        $stmt = $this->db->prepare("UPDATE categorias SET estado = ? WHERE idCategoria = ?");
        return $stmt->execute([$estado ? 1 : 0, $id]);
    }
}