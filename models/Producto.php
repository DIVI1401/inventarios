<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * El precio de venta NO se escribe a mano: se calcula a partir del costo de la receta.
 *
 *   precio = costo / (1 - margenGanancia / 100)    redondeado hacia arriba a REDONDEO
 *
 * margenGanancia es el % del precio de venta que queda como ganancia bruta.
 * En restaurantes se usa la regla del "food cost": los insumos deben ser ~30 % del
 * precio de venta, o sea un margen bruto de ~70 % (el resto paga personal, arriendo,
 * servicios y la utilidad). Por eso el margen por defecto es 70.
 */
class Producto {
    public const MARGEN_POR_DEFECTO = 70;
    public const MARGEN_MAXIMO = 95;
    public const REDONDEO = 100; // los precios terminan en centenas (ej. 12.300)

    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public static function calcularPrecio(float $costo, float $margen): float {
        if ($costo <= 0) {
            return 0;
        }
        $margen = max(0, min(self::MARGEN_MAXIMO, $margen));
        $precio = $costo / (1 - $margen / 100);
        return ceil($precio / self::REDONDEO) * self::REDONDEO;
    }

    public function listarTodos(): array {
        $sql = "SELECT p.*, c.nombre AS categoria,
                       COALESCE((SELECT SUM(ri.cantidad * i.precioUnitario)
                                 FROM recetas r
                                 INNER JOIN receta_insumos ri ON ri.idReceta = r.idReceta
                                 INNER JOIN insumos i ON i.idInsumo = ri.idInsumo
                                 WHERE r.idProducto = p.idProducto), 0) AS costoTotal
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

    // El precio arranca en 0 y se calcula cuando se guarda la receta
    public function crear(string $nombre, string $descripcion, float $margenGanancia, ?int $idCategoria): int {
        $sql = "INSERT INTO productos (nombre, descripcion, precio, margenGanancia, idCategoria, estado) VALUES (?, ?, 0, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre, $descripcion, $margenGanancia, $idCategoria]);
        return (int)$this->db->lastInsertId();
    }

    public function actualizar(int $idProducto, string $nombre, string $descripcion, float $margenGanancia, ?int $idCategoria, int $estado): bool {
        $sql = "UPDATE productos SET nombre = ?, descripcion = ?, margenGanancia = ?, idCategoria = ?, estado = ? WHERE idProducto = ?";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([$nombre, $descripcion, $margenGanancia, $idCategoria, $estado, $idProducto]);
        $this->recalcularPrecios(idProducto: $idProducto);
        return $ok;
    }

    public function cambiarEstado(int $idProducto, int $estado): bool {
        $sql = "UPDATE productos SET estado = ? WHERE idProducto = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estado, $idProducto]);
    }

    /**
     * Recalcula el precio de venta según el costo actual de la receta.
     * - con $idProducto: solo ese producto (al guardar su receta o editarlo)
     * - con $idInsumo: los productos cuya receta usa ese insumo (al cambiar su precio)
     * - sin parámetros: todos
     */
    public function recalcularPrecios(?int $idProducto = null, ?int $idInsumo = null): void {
        $sql = "SELECT p.idProducto, p.margenGanancia, SUM(ri.cantidad * i.precioUnitario) AS costoTotal
                FROM productos p
                INNER JOIN recetas r ON r.idProducto = p.idProducto
                INNER JOIN receta_insumos ri ON ri.idReceta = r.idReceta
                INNER JOIN insumos i ON i.idInsumo = ri.idInsumo
                WHERE 1 = 1";
        $params = [];
        if ($idProducto !== null) {
            $sql .= " AND p.idProducto = ?";
            $params[] = $idProducto;
        }
        if ($idInsumo !== null) {
            $sql .= " AND r.idReceta IN (SELECT idReceta FROM receta_insumos WHERE idInsumo = ?)";
            $params[] = $idInsumo;
        }
        $sql .= " GROUP BY p.idProducto, p.margenGanancia";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $upd = $this->db->prepare("UPDATE productos SET precio = ? WHERE idProducto = ?");
        foreach ($stmt->fetchAll() as $fila) {
            $precio = self::calcularPrecio((float)$fila['costoTotal'], (float)$fila['margenGanancia']);
            $upd->execute([$precio, $fila['idProducto']]);
        }
    }
}
