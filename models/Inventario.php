<?php

require_once __DIR__ . '/../config/Database.php';

class Inventario {
    private $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    // Obtiene el inventario filtrado por un almacén específico
    public function porAlmacen($idAlmacen) {
        $sql = "SELECT inv.*, i.nombre as insumo_nombre, i.unidadMedida, i.precioUnitario
                FROM inventarios inv
                INNER JOIN insumos i ON inv.idInsumo = i.idInsumo
                WHERE inv.idAlmacen = ?
                ORDER BY i.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idAlmacen]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Insumos con stock bajo (para el Dashboard)
    public function stockBajo() {
        $sql = "SELECT inv.idAlmacen, a.nombre as nombreAlmacen, 
                       inv.idInsumo, i.nombre as nombreInsumo, 
                       inv.cantidadDisponible, inv.stockMinimo, i.unidadMedida
                FROM inventarios inv
                INNER JOIN insumos i ON inv.idInsumo = i.idInsumo
                INNER JOIN almacenes a ON inv.idAlmacen = a.idAlmacen
                WHERE inv.cantidadDisponible <= inv.stockMinimo
                ORDER BY a.nombre, i.nombre ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Alertas y sugerencias de compra
    public function obtenerSugerenciasCompra() {
        $sql = "SELECT inv.idAlmacen, a.nombre as almacen_nombre, 
                       inv.idInsumo, i.nombre as insumo_nombre, 
                       inv.cantidadDisponible as stock_actual, inv.stockMinimo as stock_minimo, i.unidadMedida,
                       (inv.stockMinimo - inv.cantidadDisponible) as sugerencia_compra
                FROM inventarios inv
                INNER JOIN insumos i ON inv.idInsumo = i.idInsumo
                INNER JOIN almacenes a ON inv.idAlmacen = a.idAlmacen
                WHERE inv.cantidadDisponible <= inv.stockMinimo
                ORDER BY a.nombre, i.nombre ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Transferir stock entre almacenes
    public function transferirStock($idInsumo, $idOrigen, $idDestino, $cantidad, $idUsuario) {
        if ($cantidad <= 0 || $idOrigen == $idDestino) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $stmtOrigen = $this->db->prepare("SELECT cantidadDisponible FROM inventarios WHERE idInsumo = ? AND idAlmacen = ?");
            $stmtOrigen->execute([$idInsumo, $idOrigen]);
            $rowOrigen = $stmtOrigen->fetch(PDO::FETCH_ASSOC);

            if (!$rowOrigen || $rowOrigen['cantidadDisponible'] < $cantidad) {
                $this->db->rollBack();
                return false;
            }

            $stmtRestar = $this->db->prepare("UPDATE inventarios SET cantidadDisponible = cantidadDisponible - ? WHERE idInsumo = ? AND idAlmacen = ?");
            $stmtRestar->execute([$cantidad, $idInsumo, $idOrigen]);

            $stmtDestinoCheck = $this->db->prepare("SELECT idInventario FROM inventarios WHERE idInsumo = ? AND idAlmacen = ?");
            $stmtDestinoCheck->execute([$idInsumo, $idDestino]);
            $existeDestino = $stmtDestinoCheck->fetch(PDO::FETCH_ASSOC);

            if ($existeDestino) {
                $stmtSumar = $this->db->prepare("UPDATE inventarios SET cantidadDisponible = cantidadDisponible + ? WHERE idInsumo = ? AND idAlmacen = ?");
                $stmtSumar->execute([$cantidad, $idInsumo, $idDestino]);
            } else {
                $stmtInsertar = $this->db->prepare("INSERT INTO inventarios (idInsumo, idAlmacen, cantidadDisponible) VALUES (?, ?, ?)");
                $stmtInsertar->execute([$idInsumo, $idDestino, $cantidad]);
            }

            // Registrar el movimiento en el historial
            $stmtLog = $this->db->prepare(
                "INSERT INTO transferencias (idInsumo, idAlmacenOrigen, idAlmacenDestino, idUsuario, cantidad) 
                VALUES (?, ?, ?, ?, ?)"
            );
            $stmtLog->execute([$idInsumo, $idOrigen, $idDestino, $idUsuario, $cantidad]);
            
            // Verificar si el almacén de origen quedó bajo el mínimo, y notificar
        $stmtCheck = $this->db->prepare(
            "SELECT inv.cantidadDisponible, inv.stockMinimo, i.nombre AS nombreInsumo, a.nombre AS nombreAlmacen
             FROM inventarios inv
             INNER JOIN insumos i ON inv.idInsumo = i.idInsumo
             INNER JOIN almacenes a ON inv.idAlmacen = a.idAlmacen
             WHERE inv.idAlmacen = ? AND inv.idInsumo = ?"
        );

        $stmtCheck->execute([$idOrigen, $idInsumo]);
        $estado = $stmtCheck->fetch();

        if ($estado && $estado['cantidadDisponible'] <= $estado['stockMinimo']) {
            require_once __DIR__ . '/EmailService.php';
            EmailService::notificarStockBajo(
                $estado['nombreInsumo'],
                $estado['nombreAlmacen'],
                $estado['cantidadDisponible'],
                $estado['stockMinimo']
            );
        }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function historialTransferencias() {
        $sql = "SELECT t.fecha, t.cantidad, i.nombre AS insumo_nombre, i.unidadMedida,
                    ao.nombre AS almacen_origen, ad.nombre AS almacen_destino, u.nombre AS usuario_nombre
                FROM transferencias t
                INNER JOIN insumos i ON t.idInsumo = i.idInsumo
                INNER JOIN almacenes ao ON t.idAlmacenOrigen = ao.idAlmacen
                INNER JOIN almacenes ad ON t.idAlmacenDestino = ad.idAlmacen
                INNER JOIN usuarios u ON t.idUsuario = u.idUsuario
                ORDER BY t.fecha DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        public function listarCompleto() {
        $sql = "SELECT a.nombre as almacen_nombre, i.nombre as insumo_nombre, i.unidadMedida,
                       inv.cantidadDisponible, inv.stockMinimo
                FROM inventarios inv
                INNER JOIN insumos i ON inv.idInsumo = i.idInsumo
                INNER JOIN almacenes a ON inv.idAlmacen = a.idAlmacen
                ORDER BY a.nombre, i.nombre";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}