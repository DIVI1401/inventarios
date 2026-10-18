<?php

require_once __DIR__ . '/../config/Database.php';

// Se lanza cuando la venta no es válida (sin stock, pago insuficiente, etc.)
class VentaInvalidaException extends Exception {}

class Venta {
    public const METODOS_PAGO = ['Efectivo', 'Tarjeta', 'Transferencia'];

    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    /* ------------------------------------------------------------
     * Utilidades
     * ---------------------------------------------------------- */

    // Devuelve la fecha si tiene formato YYYY-MM-DD válido; si no, null
    public static function normalizarFecha(?string $fecha): ?string {
        if ($fecha === null || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return null;
        }
        [$y, $m, $d] = array_map('intval', explode('-', $fecha));
        return checkdate($m, $d, $y) ? $fecha : null;
    }

    // Número de comprobante legible a partir del id (ej. V-000012)
    public static function numeroComprobante(int $idVenta): string {
        return 'V-' . str_pad((string)$idVenta, 6, '0', STR_PAD_LEFT);
    }

    private function construirFiltros(?string $fechaInicio, ?string $fechaFin, ?string $metodoPago, ?int $idAlmacen): array {
        $where = [];
        $params = [];

        if ($fechaInicio) {
            $where[] = "DATE(v.fechaVenta) >= :fechaInicio";
            $params[':fechaInicio'] = $fechaInicio;
        }
        if ($fechaFin) {
            $where[] = "DATE(v.fechaVenta) <= :fechaFin";
            $params[':fechaFin'] = $fechaFin;
        }
        if ($metodoPago && in_array($metodoPago, self::METODOS_PAGO, true)) {
            $where[] = "v.metodoPago = :metodoPago";
            $params[':metodoPago'] = $metodoPago;
        }
        if ($idAlmacen) {
            $where[] = "v.idAlmacen = :idAlmacen";
            $params[':idAlmacen'] = $idAlmacen;
        }

        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }

    /* ------------------------------------------------------------
     * Historial y búsqueda de ventas
     * ---------------------------------------------------------- */

    public function listarTodas(?string $fechaInicio = null, ?string $fechaFin = null, ?string $metodoPago = null, ?int $idAlmacen = null): array {
        [$where, $params] = $this->construirFiltros($fechaInicio, $fechaFin, $metodoPago, $idAlmacen);

        $sql = "SELECT v.idVenta, v.fechaVenta, v.total, v.metodoPago,
                       a.nombre AS nombreAlmacen, u.nombre AS nombreUsuario
                FROM ventas v
                INNER JOIN almacenes a ON v.idAlmacen = a.idAlmacen
                INNER JOIN usuarios u ON v.idUsuario = u.idUsuario
                $where
                ORDER BY v.fechaVenta DESC, v.idVenta DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $sql = "SELECT v.*, a.nombre AS nombreAlmacen, a.ubicacion AS ubicacionAlmacen, u.nombre AS nombreUsuario
                FROM ventas v
                INNER JOIN almacenes a ON v.idAlmacen = a.idAlmacen
                INNER JOIN usuarios u ON v.idUsuario = u.idUsuario
                WHERE v.idVenta = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function detallesDeVenta($idVenta) {
        $sql = "SELECT dv.*, p.nombre AS nombreProducto
                FROM detalle_ventas dv
                INNER JOIN productos p ON dv.idProducto = p.idProducto
                WHERE dv.idVenta = ?
                ORDER BY dv.idDetalleVenta";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idVenta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ------------------------------------------------------------
     * Registrar venta
     *  - Toma los precios desde la BD (no confía en el navegador)
     *  - Calcula el total automáticamente
     *  - Valida y descuenta los insumos de la receta del inventario
     *  - Todo ocurre en una sola transacción: o se guarda todo o nada
     * ---------------------------------------------------------- */

    public function crear(int $idAlmacen, int $idUsuario, array $detalles, string $metodoPago, ?float $montoRecibido = null): array {
            
        $stmtAlmacen = $this->db->prepare("SELECT estado FROM almacenes WHERE idAlmacen = ?");
        $stmtAlmacen->execute([$idAlmacen]);
        $almacenActivo = $stmtAlmacen->fetchColumn();
        if (!$almacenActivo) {
            return ['exito' => false, 'error' => 'El almacén seleccionado no está activo.'];
        }
    
        if (!in_array($metodoPago, self::METODOS_PAGO, true)) {
            return ['exito' => false, 'error' => 'Método de pago no válido.'];
        }
        if (empty($detalles)) {
            return ['exito' => false, 'error' => 'Agrega al menos un producto a la venta.'];
        }

        $alertasStock = [];

        try {
            $this->db->beginTransaction();

            // 1. Validar productos y calcular el total con los precios reales
            $stmtProd = $this->db->prepare("SELECT idProducto, nombre, precio, estado FROM productos WHERE idProducto = ?");
            $items = [];
            $total = 0.0;

            foreach ($detalles as $d) {
                $cantidad = (float)$d['cantidad'];
                if ($cantidad <= 0) {
                    throw new VentaInvalidaException('La cantidad de cada producto debe ser mayor a cero.');
                }

                $stmtProd->execute([(int)$d['idProducto']]);
                $producto = $stmtProd->fetch(PDO::FETCH_ASSOC);

                if (!$producto || (int)$producto['estado'] !== 1) {
                    throw new VentaInvalidaException('Uno de los productos no existe o está inactivo.');
                }

                $precio = (float)$producto['precio'];
                if ($precio <= 0) {
                throw new VentaInvalidaException("El producto \"{$producto['nombre']}\" no tiene un precio válido configurado.");}
                $total += $cantidad * $precio;
                $items[] = [
                    'idProducto' => (int)$producto['idProducto'],
                    'nombre' => $producto['nombre'],
                    'cantidad' => $cantidad,
                    'precioUnitario' => $precio,
                ];
            }
            $total = round($total, 2);

            // 2. Validar el pago
            if ($metodoPago === 'Efectivo') {
                if ($montoRecibido === null) {
                    $montoRecibido = $total; // pago exacto si no se indica
                }
                if ($montoRecibido + 0.00001 < $total) {
                    throw new VentaInvalidaException('El monto recibido es menor al total de la venta.');
                }
            } else {
                $montoRecibido = null;
            }

            // 3. Calcular los insumos que consume la venta según las recetas
            $stmtReceta = $this->db->prepare(
                "SELECT ri.idInsumo, ri.cantidad, i.nombre AS nombreInsumo, i.unidadMedida
                 FROM recetas r
                 INNER JOIN receta_insumos ri ON ri.idReceta = r.idReceta
                 INNER JOIN insumos i ON i.idInsumo = ri.idInsumo
                 WHERE r.idProducto = ?"
            );

            $requeridos = []; // idInsumo => [nombre, unidad, cantidad]
            $avisos = [];

            foreach ($items as $it) {
                $stmtReceta->execute([$it['idProducto']]);
                $filas = $stmtReceta->fetchAll(PDO::FETCH_ASSOC);

                if (empty($filas)) {
                    throw new VentaInvalidaException("El producto \"{$it['nombre']}\" no tiene una receta asignada. Asígnale una receta antes de poder venderlo.");
                }

                foreach ($filas as $f) {
                    $id = (int)$f['idInsumo'];
                    if (!isset($requeridos[$id])) {
                        $requeridos[$id] = ['nombre' => $f['nombreInsumo'], 'unidad' => $f['unidadMedida'], 'cantidad' => 0.0];
                    }
                    $requeridos[$id]['cantidad'] += $it['cantidad'] * (float)$f['cantidad'];
                }
            }

            // 4. Verificar que haya stock suficiente (bloquea las filas para evitar ventas simultáneas)
            $stmtStock = $this->db->prepare(
                "SELECT cantidadDisponible, stockMinimo FROM inventarios
                 WHERE idAlmacen = ? AND idInsumo = ? FOR UPDATE"
            );

            $faltantes = [];
            foreach ($requeridos as $idInsumo => $r) {
                $stmtStock->execute([$idAlmacen, $idInsumo]);
                $inv = $stmtStock->fetch(PDO::FETCH_ASSOC);
                $disponible = $inv ? (float)$inv['cantidadDisponible'] : 0.0;

                if ($disponible + 0.00001 < $r['cantidad']) {
                    $faltantes[] = sprintf(
                        '%s (necesita %s %s, disponible %s)',
                        $r['nombre'],
                        self::formatoCantidad($r['cantidad']),
                        $r['unidad'],
                        self::formatoCantidad($disponible)
                    );
                }
            }

            if (!empty($faltantes)) {
                throw new VentaInvalidaException('Stock insuficiente en este almacén para: ' . implode('; ', $faltantes) . '.');
            }

            // 5. Guardar la venta y su detalle
            $stmt = $this->db->prepare(
                "INSERT INTO ventas (idAlmacen, idUsuario, total, metodoPago, montoRecibido) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$idAlmacen, $idUsuario, $total, $metodoPago, $montoRecibido]);
            $idVenta = (int)$this->db->lastInsertId();

            $stmtDetalle = $this->db->prepare(
                "INSERT INTO detalle_ventas (idVenta, idProducto, cantidad, precioUnitario) VALUES (?, ?, ?, ?)"
            );
            foreach ($items as $it) {
                $stmtDetalle->execute([$idVenta, $it['idProducto'], $it['cantidad'], $it['precioUnitario']]);
            }

            // 6. Descontar del inventario
            $stmtDescontar = $this->db->prepare(
                "UPDATE inventarios
                 SET cantidadDisponible = cantidadDisponible - ?, fechaActualizacion = NOW()
                 WHERE idAlmacen = ? AND idInsumo = ?"
            );
            $stmtEstado = $this->db->prepare(
                "SELECT cantidadDisponible, stockMinimo FROM inventarios WHERE idAlmacen = ? AND idInsumo = ?"
            );
            $stmtNombreAlmacen = $this->db->prepare("SELECT nombre FROM almacenes WHERE idAlmacen = ?");
            $stmtNombreAlmacen->execute([$idAlmacen]);
            $nombreAlmacen = (string)$stmtNombreAlmacen->fetchColumn();

            foreach ($requeridos as $idInsumo => $r) {
                $stmtDescontar->execute([$r['cantidad'], $idAlmacen, $idInsumo]);

                $stmtEstado->execute([$idAlmacen, $idInsumo]);
                $estado = $stmtEstado->fetch(PDO::FETCH_ASSOC);
                if ($estado && (float)$estado['cantidadDisponible'] <= (float)$estado['stockMinimo']) {
                    $alertasStock[] = [
                        'insumo' => $r['nombre'],
                        'almacen' => $nombreAlmacen,
                        'actual' => (float)$estado['cantidadDisponible'],
                        'minimo' => (float)$estado['stockMinimo'],
                    ];
                }
            }

            $this->db->commit();
        } catch (VentaInvalidaException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            return ['exito' => false, 'error' => $e->getMessage()];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            error_log('Error al registrar venta: ' . $e->getMessage());
            return ['exito' => false, 'error' => 'No se pudo registrar la venta por un error interno. Intenta de nuevo.'];
        }

        // Alertas de stock bajo (fuera de la transacción: un fallo de correo no afecta la venta)
        if (!empty($alertasStock)) {
            try {
                require_once __DIR__ . '/EmailService.php';
                foreach ($alertasStock as $a) {
                    EmailService::notificarStockBajo($a['insumo'], $a['almacen'], $a['actual'], $a['minimo']);
                }
            } catch (Throwable $e) {
                error_log('No se pudo enviar alerta de stock: ' . $e->getMessage());
            }
        }

        return [
            'exito' => true,
            'idVenta' => $idVenta,
            'total' => $total,
            'avisos' => array_values($avisos),
            'alertasStock' => $alertasStock,
        ];
    }

    private static function formatoCantidad(float $n): string {
        $s = rtrim(rtrim(number_format($n, 3, '.', ''), '0'), '.');
        return $s === '' ? '0' : $s;
    }

    /* ------------------------------------------------------------
     * Reportes de ventas
     * ---------------------------------------------------------- */

    public function reporteResumen(?string $fechaInicio, ?string $fechaFin, ?string $metodoPago, ?int $idAlmacen): array {
        [$where, $params] = $this->construirFiltros($fechaInicio, $fechaFin, $metodoPago, $idAlmacen);

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS numVentas,
                    COALESCE(SUM(v.total), 0) AS totalVendido,
                    COALESCE(AVG(v.total), 0) AS ticketPromedio
             FROM ventas v $where"
        );
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function reportePorMetodoPago(?string $fechaInicio, ?string $fechaFin, ?string $metodoPago, ?int $idAlmacen): array {
        [$where, $params] = $this->construirFiltros($fechaInicio, $fechaFin, $metodoPago, $idAlmacen);

        $stmt = $this->db->prepare(
            "SELECT v.metodoPago, COUNT(*) AS numVentas, SUM(v.total) AS totalVendido
             FROM ventas v $where
             GROUP BY v.metodoPago
             ORDER BY totalVendido DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reportePorDia(?string $fechaInicio, ?string $fechaFin, ?string $metodoPago, ?int $idAlmacen): array {
        [$where, $params] = $this->construirFiltros($fechaInicio, $fechaFin, $metodoPago, $idAlmacen);

        $stmt = $this->db->prepare(
            "SELECT DATE(v.fechaVenta) AS dia, COUNT(*) AS numVentas, SUM(v.total) AS totalVendido
             FROM ventas v $where
             GROUP BY DATE(v.fechaVenta)
             ORDER BY dia DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reporteProductosMasVendidos(?string $fechaInicio, ?string $fechaFin, ?string $metodoPago, ?int $idAlmacen, int $limite = 10): array {
        [$where, $params] = $this->construirFiltros($fechaInicio, $fechaFin, $metodoPago, $idAlmacen);
        $limite = max(1, $limite);

        $stmt = $this->db->prepare(
            "SELECT p.nombre AS nombreProducto,
                    SUM(dv.cantidad) AS unidadesVendidas,
                    SUM(dv.subtotal) AS totalVendido
             FROM detalle_ventas dv
             INNER JOIN ventas v ON v.idVenta = dv.idVenta
             INNER JOIN productos p ON p.idProducto = dv.idProducto
             $where
             GROUP BY p.idProducto, p.nombre
             ORDER BY unidadesVendidas DESC, totalVendido DESC
             LIMIT $limite"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
