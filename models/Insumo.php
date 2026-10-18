<?php

require_once __DIR__ . '/../config/Database.php';

class Insumo {
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

   public function listarTodos() {
        $stmt = $this->db->query(
            "SELECT i.*, c.nombre AS nombreCategoria 
             FROM insumos i
             LEFT JOIN categorias c ON i.idCategoria = c.idCategoria
             ORDER BY i.nombre ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorCategoria($idCategoria) {
        $stmt = $this->db->prepare(
            "SELECT i.*, c.nombre AS nombreCategoria 
             FROM insumos i
             LEFT JOIN categorias c ON i.idCategoria = c.idCategoria
             WHERE i.idCategoria = ?
             ORDER BY i.nombre ASC"
        );
        $stmt->execute([$idCategoria]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function buscarPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM insumos WHERE idInsumo = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function validar($nombre, $precioUnitario, $fechaVencimiento) {
        $errores = [];

        if (empty($nombre)) {
            $errores[] = "El nombre del insumo es obligatorio.";
        }

        if ($precioUnitario <= 0) {
            $errores[] = "El precio unitario debe ser mayor a cero.";
        }

        if (!empty($fechaVencimiento) && $fechaVencimiento < date('Y-m-d')) {
            $errores[] = "La fecha de vencimiento no puede ser anterior a hoy.";
        }

        return $errores;
    }

    public function crear($nombre, $unidadMedida, $precioUnitario, $stockMinimo, $fechaVencimiento, $idCategoria) {
        $sql = "INSERT INTO insumos (nombre, unidadMedida, precioUnitario, fechaVencimiento, idCategoria, estado) 
                VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre, $unidadMedida, $precioUnitario, $fechaVencimiento ?: null, $idCategoria ?: null]);

        $idInsumo = (int)$this->db->lastInsertId();
        $this->propagarStockMinimo($idInsumo, $stockMinimo);

        return true;
    }

    public function editar($id, $nombre, $unidadMedida, $precioUnitario, $stockMinimo, $fechaVencimiento, $idCategoria) {
        $sql = "UPDATE insumos 
                SET nombre = ?, unidadMedida = ?, precioUnitario = ?, fechaVencimiento = ?, idCategoria = ?
                WHERE idInsumo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre, $unidadMedida, $precioUnitario, $fechaVencimiento ?: null, $idCategoria ?: null, $id]);

        $this->propagarStockMinimo($id, $stockMinimo);

        return true;
    }
    // Escribe (o actualiza) el stock mínimo de este insumo en TODOS los almacenes.
    // Si el almacén todavía no tiene este insumo en inventario, lo crea con cantidad 0.
    private function propagarStockMinimo($idInsumo, $stockMinimo) {
        $sql = "INSERT INTO inventarios (idAlmacen, idInsumo, cantidadDisponible, stockMinimo)
                SELECT idAlmacen, ?, 0, ? FROM almacenes
                ON DUPLICATE KEY UPDATE stockMinimo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idInsumo, $stockMinimo, $stockMinimo]);
    }

    public function cambiarEstado($id, $estado) {
        $stmt = $this->db->prepare("UPDATE insumos SET estado = ? WHERE idInsumo = ?");
        return $stmt->execute([$estado ? 1 : 0, $id]);
    }

    public function proximosAVencer() {
        $sql = "SELECT * FROM insumos 
                WHERE fechaVencimiento IS NOT NULL 
                AND fechaVencimiento >= CURDATE() 
                ORDER BY fechaVencimiento ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}