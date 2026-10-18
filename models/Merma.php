<?php

require_once __DIR__ . '/../config/Database.php';

class Merma {
    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function listarTodas(): array {
        $stmt = $this->db->query(
            "SELECT m.idMerma, m.cantidad, m.motivo, m.fechaMerma,
                    i.nombre AS nombreInsumo, i.unidadMedida,
                    a.nombre AS nombreAlmacen, u.nombre AS nombreUsuario
             FROM mermas m
             INNER JOIN insumos i ON m.idInsumo = i.idInsumo
             INNER JOIN almacenes a ON m.idAlmacen = a.idAlmacen
             INNER JOIN usuarios u ON m.idUsuario = u.idUsuario
             ORDER BY m.fechaMerma DESC"
        );
        return $stmt->fetchAll();
    }

    // Cantidad disponible actual de un insumo en un almacén específico
    public function stockDisponible(int $idAlmacen, int $idInsumo): float {
        $stmt = $this->db->prepare(
            "SELECT cantidadDisponible FROM inventarios 
             WHERE idAlmacen = :idAlmacen AND idInsumo = :idInsumo"
        );
        $stmt->execute(['idAlmacen' => $idAlmacen, 'idInsumo' => $idInsumo]);
        $resultado = $stmt->fetch();
        return $resultado ? (float)$resultado['cantidadDisponible'] : 0.0;
    }

    public function crear(int $idInsumo, int $idAlmacen, int $idUsuario, float $cantidad, string $motivo): array {
        $disponible = $this->stockDisponible($idAlmacen, $idInsumo);

        if ($cantidad > $disponible) {
            return ['exito' => false, 'error' => "No hay suficiente stock. Disponible: {$disponible}"];
        }

        try {
            $this->db->beginTransaction();

            // 1. Registrar la merma
            $stmt = $this->db->prepare(
                "INSERT INTO mermas (idInsumo, idAlmacen, idUsuario, cantidad, motivo) 
                 VALUES (:idInsumo, :idAlmacen, :idUsuario, :cantidad, :motivo)"
            );
            $stmt->execute([
                'idInsumo' => $idInsumo,
                'idAlmacen' => $idAlmacen,
                'idUsuario' => $idUsuario,
                'cantidad' => $cantidad,
                'motivo' => $motivo,
            ]);

            // 2. Descontar del inventario
            $stmtInventario = $this->db->prepare(
                "UPDATE inventarios SET 
                    cantidadDisponible = cantidadDisponible - :cantidad,
                    fechaActualizacion = NOW()
                 WHERE idAlmacen = :idAlmacen AND idInsumo = :idInsumo"
            );
            $stmtInventario->execute([
                'cantidad' => $cantidad,
                'idAlmacen' => $idAlmacen,
                'idInsumo' => $idInsumo,
            ]);

            $stmtCheck = $this->db->prepare(
                    "SELECT inv.cantidadDisponible, inv.stockMinimo, i.nombre AS nombreInsumo, a.nombre AS nombreAlmacen
                    FROM inventarios inv
                    INNER JOIN insumos i ON inv.idInsumo = i.idInsumo
                    INNER JOIN almacenes a ON inv.idAlmacen = a.idAlmacen
                    WHERE inv.idAlmacen = :idAlmacen AND inv.idInsumo = :idInsumo"
                );
                
            $stmtCheck->execute(['idAlmacen' => $idAlmacen, 'idInsumo' => $idInsumo]);
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
            return ['exito' => true];

        } catch (Exception $e) {
            $this->db->rollBack();
            return ['exito' => false, 'error' => "Error al registrar la merma."];
        }
    }

    // Requisito 36: reporte de mermas por periodo
    public function porPeriodo(string $fechaInicio, string $fechaFin): array {
        $stmt = $this->db->prepare(
            "SELECT m.*, i.nombre AS nombreInsumo, a.nombre AS nombreAlmacen
             FROM mermas m
             INNER JOIN insumos i ON m.idInsumo = i.idInsumo
             INNER JOIN almacenes a ON m.idAlmacen = a.idAlmacen
             WHERE m.fechaMerma BETWEEN :inicio AND :fin
             ORDER BY m.fechaMerma DESC"
        );
        $stmt->execute(['inicio' => $fechaInicio, 'fin' => $fechaFin]);
        return $stmt->fetchAll();
    }

    // Requisito 36: Obtener reporte con filtros dinámicos por almacén y rango de fechas
    public function obtenerReporte(?int $idAlmacen = null, ?string $fechaInicio = null, ?string $fechaFin = null): array {
        $sql = "SELECT m.*, i.nombre AS nombreInsumo, i.unidadMedida, a.nombre AS nombreAlmacen, u.nombre AS nombreUsuario 
                FROM mermas m
                INNER JOIN insumos i ON m.idInsumo = i.idInsumo
                INNER JOIN almacenes a ON m.idAlmacen = a.idAlmacen
                INNER JOIN usuarios u ON m.idUsuario = u.idUsuario
                WHERE 1=1";
        
        $params = [];

        if ($idAlmacen) {
            $sql .= " AND m.idAlmacen = :idAlmacen";
            $params[':idAlmacen'] = $idAlmacen;
        }

        if ($fechaInicio && $fechaFin) {
            $sql .= " AND DATE(m.fechaMerma) BETWEEN :fechaInicio AND :fechaFin";
            $params[':fechaInicio'] = $fechaInicio;
            $params[':fechaFin'] = $fechaFin;
        }

        $sql .= " ORDER BY m.fechaMerma DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listarPorAlmacen(?int $idAlmacen = null): array {
    $sql = "SELECT m.idMerma, m.cantidad, m.motivo, m.fechaMerma,
                   i.nombre AS nombreInsumo, i.unidadMedida,
                   a.nombre AS nombreAlmacen, u.nombre AS nombreUsuario
            FROM mermas m
            INNER JOIN insumos i ON m.idInsumo = i.idInsumo
            INNER JOIN almacenes a ON m.idAlmacen = a.idAlmacen
            INNER JOIN usuarios u ON m.idUsuario = u.idUsuario";

    $params = [];
    if ($idAlmacen) {
        $sql .= " WHERE m.idAlmacen = :idAlmacen";
        $params['idAlmacen'] = $idAlmacen;
    }

    $sql .= " ORDER BY m.fechaMerma DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

}