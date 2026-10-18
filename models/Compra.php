<?php

require_once __DIR__ . '/../config/Database.php';

class Compra {
    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function listarTodas(): array {
        $stmt = $this->db->query(
            "SELECT c.idCompra, c.fechaCompra, c.total, p.nombre AS nombreProveedor, a.nombre AS nombreAlmacen
             FROM compras c
             INNER JOIN proveedores p ON c.idProveedor = p.idProveedor
             INNER JOIN almacenes a ON c.idAlmacen = a.idAlmacen
             ORDER BY c.fechaCompra DESC"
        );
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT c.*, p.nombre AS nombreProveedor, a.nombre AS nombreAlmacen
             FROM compras c
             INNER JOIN proveedores p ON c.idProveedor = p.idProveedor
             INNER JOIN almacenes a ON c.idAlmacen = a.idAlmacen
             WHERE c.idCompra = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function detallesDeCompra(int $idCompra): array {
        $stmt = $this->db->prepare(
            "SELECT dc.*, i.nombre AS nombreInsumo, i.unidadMedida
             FROM detalle_compras dc
             INNER JOIN insumos i ON dc.idInsumo = i.idInsumo
             WHERE dc.idCompra = :idCompra"
        );
        $stmt->execute(['idCompra' => $idCompra]);
        return $stmt->fetchAll();
    }

    /**
     * $detalles = [ ['idInsumo' => 1, 'cantidad' => 10, 'precioUnitario' => 2500], ... ]
     */
    public function crear(int $idProveedor, int $idAlmacen, int $idUsuario, array $detalles): bool {
        try {
            $this->db->beginTransaction();

            $total = 0;
            foreach ($detalles as $d) {
                $total += $d['cantidad'] * $d['precioUnitario'];
            }

            // 1. Insertar cabecera de la compra
            $stmt = $this->db->prepare(
                "INSERT INTO compras (idProveedor, idAlmacen, idUsuario, total) 
                 VALUES (:idProveedor, :idAlmacen, :idUsuario, :total)"
            );
            $stmt->execute([
                'idProveedor' => $idProveedor,
                'idAlmacen' => $idAlmacen,
                'idUsuario' => $idUsuario,
                'total' => $total,
            ]);

            $idCompra = (int)$this->db->lastInsertId();

            // 2. Insertar cada línea de detalle
            $stmtDetalle = $this->db->prepare(
                "INSERT INTO detalle_compras (idCompra, idInsumo, cantidad, precioUnitario) 
                 VALUES (:idCompra, :idInsumo, :cantidad, :precioUnitario)"
            );

            // 3. Actualizar (o crear) el registro en 'inventarios' para ese almacén + insumo
            $stmtInventario = $this->db->prepare(
                "INSERT INTO inventarios (idAlmacen, idInsumo, cantidadDisponible) 
                 VALUES (:idAlmacen, :idInsumo, :cantidad)
                 ON DUPLICATE KEY UPDATE 
                    cantidadDisponible = cantidadDisponible + :cantidad2"
            );

            foreach ($detalles as $d) {
                $stmtDetalle->execute([
                    'idCompra' => $idCompra,
                    'idInsumo' => $d['idInsumo'],
                    'cantidad' => $d['cantidad'],
                    'precioUnitario' => $d['precioUnitario'],
                ]);

                $stmtInventario->execute([
                    'idAlmacen' => $idAlmacen,
                    'idInsumo' => $d['idInsumo'],
                    'cantidad' => $d['cantidad'],
                    'cantidad2' => $d['cantidad'],
                ]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Devuelve el último precio pagado por cada combinación proveedor+insumo
    public function ultimosPreciosPorProveedor(): array {
        $sql = "SELECT dc.idInsumo, c.idProveedor, dc.precioUnitario
                FROM detalle_compras dc
                INNER JOIN compras c ON dc.idCompra = c.idCompra
                INNER JOIN (
                    SELECT dc2.idInsumo, c2.idProveedor, MAX(c2.fechaCompra) AS ultimaFecha
                    FROM detalle_compras dc2
                    INNER JOIN compras c2 ON dc2.idCompra = c2.idCompra
                    GROUP BY dc2.idInsumo, c2.idProveedor
                ) ultimo ON ultimo.idInsumo = dc.idInsumo 
                         AND ultimo.idProveedor = c.idProveedor 
                         AND ultimo.ultimaFecha = c.fechaCompra";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    // Requisito 48: gasto en compras del mes actual
    public function gastoDelMes(): float {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(total), 0) AS gastoTotal
             FROM compras
             WHERE MONTH(fechaCompra) = MONTH(CURDATE())
             AND YEAR(fechaCompra) = YEAR(CURDATE())"
        );
        return (float)$stmt->fetch()['gastoTotal'];
    }

    // Requisito 49: Reporte de compras por proveedor y periodo
    public function obtenerReportePorProveedor(?int $idProveedor = null, ?string $fechaInicio = null, ?string $fechaFin = null): array {
        $sql = "SELECT c.*, p.nombre AS nombreProveedor, u.nombre AS nombreUsuario, a.nombre AS nombreAlmacen,
                       (SELECT COUNT(*) FROM detalle_compras dc WHERE dc.idCompra = c.idCompra) AS totalItems
                FROM compras c
                INNER JOIN proveedores p ON c.idProveedor = p.idProveedor
                INNER JOIN almacenes a ON c.idAlmacen = a.idAlmacen
                INNER JOIN usuarios u ON c.idUsuario = u.idUsuario
                WHERE 1=1";

        $params = [];

        if ($idProveedor) {
            $sql .= " AND c.idProveedor = :idProveedor";
            $params[':idProveedor'] = $idProveedor;
        }

        if ($fechaInicio && $fechaFin) {
            $sql .= " AND DATE(c.fechaCompra) BETWEEN :fechaInicio AND :fechaFin";
            $params[':fechaInicio'] = $fechaInicio;
            $params[':fechaFin'] = $fechaFin;
        }

        $sql .= " ORDER BY c.fechaCompra DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

     // Requisito 39: historial de precios de compra por insumo
    public function historialPrecioPorInsumo(int $idInsumo): array {
        $sql = "SELECT dc.precioUnitario, dc.cantidad, c.fechaCompra, p.nombre AS nombreProveedor
                FROM detalle_compras dc
                INNER JOIN compras c ON dc.idCompra = c.idCompra
                INNER JOIN proveedores p ON c.idProveedor = p.idProveedor
                WHERE dc.idInsumo = :idInsumo
                ORDER BY c.fechaCompra DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idInsumo' => $idInsumo]);
        return $stmt->fetchAll();
    }

    // Requisito 40: comparar precios entre proveedores de un mismo insumo
    public function comparativaPrecioPorInsumo(int $idInsumo): array {
        $sql = "SELECT p.nombre AS nombreProveedor, 
                       COUNT(*) AS vecesComprado,
                       AVG(dc.precioUnitario) AS precioPromedio,
                       MIN(dc.precioUnitario) AS precioMinimo,
                       MAX(dc.precioUnitario) AS precioMaximo,
                       MAX(c.fechaCompra) AS ultimaCompra
                FROM detalle_compras dc
                INNER JOIN compras c ON dc.idCompra = c.idCompra
                INNER JOIN proveedores p ON c.idProveedor = p.idProveedor
                WHERE dc.idInsumo = :idInsumo
                GROUP BY p.idProveedor, p.nombre
                ORDER BY precioPromedio ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idInsumo' => $idInsumo]);
        return $stmt->fetchAll();
    }
}