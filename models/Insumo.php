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
    // Incluye el stock mínimo actual (es el mismo en todos los almacenes, ver propagarStockMinimo)
    public function buscarPorId($id) {
        $stmt = $this->db->prepare(
            "SELECT i.*,
                    COALESCE((SELECT MAX(inv.stockMinimo) FROM inventarios inv WHERE inv.idInsumo = i.idInsumo), 0) AS stockMinimo
             FROM insumos i
             WHERE i.idInsumo = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function validar($nombre, $precioUnitario, $stockMinimo = 0) {
        $errores = [];

        if (empty($nombre)) {
            $errores[] = "El nombre del insumo es obligatorio.";
        }

        if ($precioUnitario <= 0) {
            $errores[] = "El precio unitario debe ser mayor a cero.";
        }

        if ($stockMinimo < 0) {
            $errores[] = "El stock mínimo no puede ser negativo.";
        }

        return $errores;
    }

    // La fecha de vencimiento ya no va aquí: se registra por lote en cada compra (detalle_compras)
    public function crear($nombre, $unidadMedida, $precioUnitario, $stockMinimo, $idCategoria) {
        $sql = "INSERT INTO insumos (nombre, unidadMedida, precioUnitario, idCategoria, estado) 
                VALUES (?, ?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre, $unidadMedida, $precioUnitario, $idCategoria ?: null]);

        $idInsumo = (int)$this->db->lastInsertId();
        $this->propagarStockMinimo($idInsumo, $stockMinimo);

        return true;
    }

    public function editar($id, $nombre, $unidadMedida, $precioUnitario, $stockMinimo, $idCategoria) {
        $sql = "UPDATE insumos 
                SET nombre = ?, unidadMedida = ?, precioUnitario = ?, idCategoria = ?
                WHERE idInsumo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre, $unidadMedida, $precioUnitario, $idCategoria ?: null, $id]);

        $this->propagarStockMinimo($id, $stockMinimo);

        return true;
    }
    // Escribe (o actualiza) el stock mínimo de este insumo en TODOS los almacenes.
    // Si el almacén todavía no tiene este insumo en inventario, lo crea con cantidad 0.
    private function propagarStockMinimo($idInsumo, $stockMinimo) {
        $sql = "INSERT INTO inventarios (idAlmacen, idInsumo, cantidadDisponible, stockMinimo)
                SELECT idAlmacen, CAST(? AS INTEGER), 0, CAST(? AS NUMERIC) FROM almacenes
                ON CONFLICT (idAlmacen, idInsumo) DO UPDATE SET stockMinimo = EXCLUDED.stockMinimo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idInsumo, (float)$stockMinimo]);
    }

    public function cambiarEstado($id, $estado) {
        $stmt = $this->db->prepare("UPDATE insumos SET estado = ? WHERE idInsumo = ?");
        return $stmt->execute([$estado ? 1 : 0, $id]);
    }

    /**
     * Lotes comprados que vencen en los próximos $dias días (o que ya vencieron)
     * y cuyo insumo todavía tiene existencias en el almacén donde se recibió.
     */
    public function proximosAVencer(int $dias = 7): array {
        $sql = "SELECT i.idInsumo, i.nombre, i.unidadMedida, a.nombre AS nombreAlmacen,
                       dc.fechaVencimiento, dc.cantidad, c.idCompra, inv.cantidadDisponible,
                       (dc.fechaVencimiento - CURRENT_DATE) AS diasRestantes
                FROM detalle_compras dc
                INNER JOIN compras c     ON c.idCompra = dc.idCompra
                INNER JOIN insumos i     ON i.idInsumo = dc.idInsumo
                INNER JOIN almacenes a   ON a.idAlmacen = c.idAlmacen
                INNER JOIN inventarios inv ON inv.idAlmacen = c.idAlmacen AND inv.idInsumo = dc.idInsumo
                WHERE dc.fechaVencimiento IS NOT NULL
                  AND dc.fechaVencimiento <= CURRENT_DATE + CAST(? AS INTEGER)
                  AND dc.fechaVencimiento >= CURRENT_DATE - 30
                  AND inv.cantidadDisponible > 0
                  AND i.estado = 1
                ORDER BY dc.fechaVencimiento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
