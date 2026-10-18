<?php

require_once __DIR__ . '/../config/Database.php';

class Receta {
    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function obtenerPorProducto(int $idProducto): ?array {
        $sql = "SELECT * FROM recetas WHERE idProducto = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idProducto]);
        return $stmt->fetch() ?: null;
    }

    public function obtenerInsumosDeReceta(int $idReceta): array {
        $sql = "SELECT ri.*, i.nombre AS insumo, i.unidadMedida 
                FROM receta_insumos ri 
                JOIN insumos i ON ri.idInsumo = i.idInsumo 
                WHERE ri.idReceta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idReceta]);
        return $stmt->fetchAll();
    }

    public function guardarReceta(int $idProducto, string $instrucciones, array $insumos, array $cantidades): bool {
        try {
            $this->db->beginTransaction();

            // Verificar si ya existe receta para este producto
            $recetaExistente = $this->obtenerPorProducto($idProducto);

            if ($recetaExistente) {
                $idReceta = $recetaExistente['idReceta'];
                $sqlUpd = "UPDATE recetas SET instrucciones = ? WHERE idReceta = ?";
                $stmtUpd = $this->db->prepare($sqlUpd);
                $stmtUpd->execute([$instrucciones, $idReceta]);

                // Limpiar insumos antiguos para reinsertar
                $sqlDel = "DELETE FROM receta_insumos WHERE idReceta = ?";
                $stmtDel = $this->db->prepare($sqlDel);
                $stmtDel->execute([$idReceta]);
            } else {
                $sqlIns = "INSERT INTO recetas (idProducto, instrucciones) VALUES (?, ?)";
                $stmtIns = $this->db->prepare($sqlIns);
                $stmtIns->execute([$idProducto, $instrucciones]);
                $idReceta = (int)$this->db->lastInsertId();
            }

            // Insertar insumos con sus cantidades
            $sqlItem = "INSERT INTO receta_insumos (idReceta, idInsumo, cantidad) VALUES (?, ?, ?)";
            $stmtItem = $this->db->prepare($sqlItem);

            foreach ($insumos as $index => $idInsumo) {
                $cant = floatval($cantidades[$index] ?? 0);
                if ($idInsumo > 0 && $cant > 0) {
                    $stmtItem->execute([$idReceta, $idInsumo, $cant]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
        public function eliminarReceta(int $idReceta): bool {
        // receta_insumos se borra sola gracias al ON DELETE CASCADE de la FK
        $sql = "DELETE FROM recetas WHERE idReceta = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idReceta]);
    }

        public function calcularCosto(int $idReceta): float {
        $sql = "SELECT SUM(ri.cantidad * i.precioUnitario) AS costoTotal
                FROM receta_insumos ri
                INNER JOIN insumos i ON ri.idInsumo = i.idInsumo
                WHERE ri.idReceta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idReceta]);
        $resultado = $stmt->fetch();
        return (float)($resultado['costoTotal'] ?? 0);
    }

        public function calcularStockDisponible(int $idReceta, int $idAlmacen): int {
        $sql = "SELECT ri.cantidad AS cantidadRequerida, 
                       COALESCE(inv.cantidadDisponible, 0) AS disponible
                FROM receta_insumos ri
                LEFT JOIN inventarios inv ON inv.idInsumo = ri.idInsumo AND inv.idAlmacen = ?
                WHERE ri.idReceta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idAlmacen, $idReceta]);
        $filas = $stmt->fetchAll();

        if (empty($filas)) return 0;

        $minimo = null;
        foreach ($filas as $f) {
            if ($f['cantidadRequerida'] <= 0) continue;
            $posibles = floor($f['disponible'] / $f['cantidadRequerida']);
            $minimo = ($minimo === null) ? $posibles : min($minimo, $posibles);
        }

        return (int)($minimo ?? 0);
    }
}