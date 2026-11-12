<?php

require_once __DIR__ . '/../config/Database.php';

/**
 * Reglas comunes de estado para los catálogos (usuarios, almacenes, categorías,
 * insumos, proveedores y productos):
 *
 *  - estaActivo(): para no usar registros desactivados en compras, mermas, etc.
 *  - eliminar():   borrado definitivo, permitido SOLO si el registro ya está
 *                  desactivado y no tiene historial que dependa de él. Si tiene
 *                  historial se queda desactivado (así no se pierden compras,
 *                  ventas ni la auditoría).
 */
class Registro {

    /**
     * tabla, llave primaria, etiqueta para mensajes, dependencias que bloquean
     * el borrado [tabla, columna, descripción, condición extra] y tablas que se
     * limpian antes de borrar (filas sin información útil).
     */
    private const ENTIDADES = [
        'usuarios' => [
            'tabla' => 'usuarios', 'pk' => 'idUsuario', 'nombre' => 'el usuario',
            'dependencias' => [
                ['compras', 'idUsuario', 'compras registradas'],
                ['ventas', 'idUsuario', 'ventas registradas'],
                ['mermas', 'idUsuario', 'mermas registradas'],
                ['transferencias', 'idUsuario', 'transferencias registradas'],
                ['logs_actividad', 'idUsuario', 'registros en la auditoría'],
            ],
            'limpiar' => [],
        ],
        'almacenes' => [
            'tabla' => 'almacenes', 'pk' => 'idAlmacen', 'nombre' => 'el almacén',
            'dependencias' => [
                ['inventarios', 'idAlmacen', 'insumos con existencias', 'cantidadDisponible > 0'],
                ['compras', 'idAlmacen', 'compras'],
                ['ventas', 'idAlmacen', 'ventas'],
                ['mermas', 'idAlmacen', 'mermas'],
                ['transferencias', 'idAlmacenOrigen', 'transferencias de salida'],
                ['transferencias', 'idAlmacenDestino', 'transferencias de entrada'],
            ],
            'limpiar' => [['inventarios', 'idAlmacen']], // filas en 0 creadas por el stock mínimo
        ],
        'categorias' => [
            'tabla' => 'categorias', 'pk' => 'idCategoria', 'nombre' => 'la categoría',
            'dependencias' => [
                ['insumos', 'idCategoria', 'insumos asignados'],
                ['productos', 'idCategoria', 'productos asignados'],
            ],
            'limpiar' => [],
        ],
        'insumos' => [
            'tabla' => 'insumos', 'pk' => 'idInsumo', 'nombre' => 'el insumo',
            'dependencias' => [
                ['inventarios', 'idInsumo', 'existencias en algún almacén', 'cantidadDisponible > 0'],
                ['detalle_compras', 'idInsumo', 'compras'],
                ['mermas', 'idInsumo', 'mermas'],
                ['transferencias', 'idInsumo', 'transferencias'],
                ['receta_insumos', 'idInsumo', 'recetas que lo usan'],
            ],
            'limpiar' => [['inventarios', 'idInsumo']],
        ],
        'proveedores' => [
            'tabla' => 'proveedores', 'pk' => 'idProveedor', 'nombre' => 'el proveedor',
            'dependencias' => [
                ['compras', 'idProveedor', 'compras'],
            ],
            'limpiar' => [],
        ],
        'productos' => [
            'tabla' => 'productos', 'pk' => 'idProducto', 'nombre' => 'el producto',
            'dependencias' => [
                ['detalle_ventas', 'idProducto', 'ventas'],
            ],
            'limpiar' => [], // su receta se borra sola (ON DELETE CASCADE)
        ],
    ];

    private static function config(string $entidad): array {
        if (!isset(self::ENTIDADES[$entidad])) {
            throw new InvalidArgumentException("Entidad desconocida: {$entidad}");
        }
        return self::ENTIDADES[$entidad];
    }

    /** true si el registro existe y está activo (estado = 1). */
    public static function estaActivo(string $entidad, ?int $id): bool {
        if (!$id) {
            return false;
        }
        $c = self::config($entidad);
        $stmt = Database::conectar()->prepare("SELECT estado FROM {$c['tabla']} WHERE {$c['pk']} = ?");
        $stmt->execute([$id]);
        $estado = $stmt->fetchColumn();
        return $estado !== false && (int)$estado === 1;
    }

    /**
     * Deja solo las filas activas para llenar un <select>. $conservar permite
     * mantener los ids que el registro ya tenía (ej. la categoría actual de un
     * insumo aunque ahora esté desactivada), para no perderla al editar.
     */
    public static function filtrarActivos(array $filas, string $pk, array $conservar = []): array {
        return array_values(array_filter(
            $filas,
            fn($f) => (int)$f['estado'] === 1 || in_array((int)$f[$pk], array_map('intval', $conservar), true)
        ));
    }

    /** true si todos los ids existen y están activos. */
    public static function todosActivos(string $entidad, array $ids): bool {
        foreach (array_unique(array_map('intval', $ids)) as $id) {
            if (!self::estaActivo($entidad, $id)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Intenta borrar definitivamente. Devuelve ['exito' => bool, 'mensaje' => string].
     */
    public static function eliminar(string $entidad, int $id): array {
        $c = self::config($entidad);
        $db = Database::conectar();

        $stmt = $db->prepare("SELECT estado FROM {$c['tabla']} WHERE {$c['pk']} = ?");
        $stmt->execute([$id]);
        $estado = $stmt->fetchColumn();

        if ($estado === false) {
            return ['exito' => false, 'mensaje' => ucfirst($c['nombre']) . ' no existe.'];
        }
        if ((int)$estado === 1) {
            return ['exito' => false, 'mensaje' => 'Primero debes desactivar ' . $c['nombre'] . ' antes de eliminarlo.'];
        }

        $motivos = [];
        foreach ($c['dependencias'] as $dep) {
            [$tabla, $columna, $descripcion] = $dep;
            $sql = "SELECT COUNT(*) FROM {$tabla} WHERE {$columna} = ?" . (isset($dep[3]) ? " AND {$dep[3]}" : '');
            $q = $db->prepare($sql);
            $q->execute([$id]);
            $total = (int)$q->fetchColumn();
            if ($total > 0) {
                $motivos[] = "{$total} {$descripcion}";
            }
        }

        if ($motivos) {
            return ['exito' => false, 'mensaje' => 'No se puede eliminar ' . $c['nombre'] . ' porque tiene historial ('
                . implode(', ', $motivos) . '). Se queda desactivado para no perder esa información.'];
        }

        try {
            $db->beginTransaction();
            foreach ($c['limpiar'] as [$tabla, $columna]) {
                $db->prepare("DELETE FROM {$tabla} WHERE {$columna} = ?")->execute([$id]);
            }
            $db->prepare("DELETE FROM {$c['tabla']} WHERE {$c['pk']} = ?")->execute([$id]);
            $db->commit();
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("[eliminar {$entidad} {$id}] " . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'No se pudo eliminar ' . $c['nombre'] . ': tiene datos relacionados.'];
        }

        return ['exito' => true, 'mensaje' => ucfirst($c['nombre']) . ' se eliminó definitivamente.'];
    }
}
