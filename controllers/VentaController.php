<?php

require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Almacen.php';
require_once __DIR__ . '/../models/Log.php';

class VentaController {
    private Venta $ventaModel;

    public function __construct() {
        $this->ventaModel = new Venta();
    }

    // Lee y valida los filtros del historial (fechas, método de pago, almacén)
    private function leerFiltros(): array {
        $fechaInicio = Venta::normalizarFecha($_GET['fechaInicio'] ?? null);
        $fechaFin = Venta::normalizarFecha($_GET['fechaFin'] ?? null);

        // Si las fechas vienen al revés, se invierten
        if ($fechaInicio && $fechaFin && $fechaInicio > $fechaFin) {
            [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
        }

        $metodoPago = $_GET['metodoPago'] ?? '';
        if (!in_array($metodoPago, Venta::METODOS_PAGO, true)) {
            $metodoPago = '';
        }

        $idAlmacen = isset($_GET['idAlmacen']) && $_GET['idAlmacen'] !== '' ? (int)$_GET['idAlmacen'] : null;

        return [$fechaInicio, $fechaFin, $metodoPago, $idAlmacen];
    }

    // Historial de ventas con búsqueda por fecha
    public function listar(): void {
        [$fechaInicio, $fechaFin, $metodoPago, $idAlmacen] = $this->leerFiltros();

        $ventas = $this->ventaModel->listarTodas($fechaInicio, $fechaFin, $metodoPago, $idAlmacen);
        $almacenes = (new Almacen())->listarTodos();
        $metodosPago = Venta::METODOS_PAGO;

        $totalFiltrado = 0.0;
        foreach ($ventas as $v) {
            $totalFiltrado += (float)$v['total'];
        }

        require __DIR__ . '/../views/ventas/index.php';
    }

    // Productos y almacenes activos + métodos de pago para el formulario de venta
    private function datosFormulario(): array {
        $productos = array_values(array_filter(
            (new Producto())->listarTodos(),
            fn($p) => (int)$p['estado'] === 1
        ));
        $almacenes = array_values(array_filter(
            (new Almacen())->listarTodos(),
            fn($a) => (int)$a['estado'] === 1
        ));
        return [$productos, $almacenes, Venta::METODOS_PAGO];
    }

    public function mostrarFormularioCrear(): void {
        [$productos, $almacenes, $metodosPago] = $this->datosFormulario();
        $error = null;
        $old = [];
        require __DIR__ . '/../views/ventas/crear.php';
    }

    public function crear(): void {
        $idAlmacen = (int)($_POST['idAlmacen'] ?? 0);
        $idUsuario = (int)$_SESSION['idUsuario'];
        $metodoPago = $_POST['metodoPago'] ?? '';
        $montoRecibido = (isset($_POST['montoRecibido']) && $_POST['montoRecibido'] !== '')
            ? (float)$_POST['montoRecibido']
            : null;

        $idsProducto = $_POST['idProducto'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];

        // Agrupa filas repetidas del mismo producto
        $agrupados = [];
        foreach ((array)$idsProducto as $i => $idProducto) {
            $idProducto = (int)$idProducto;
            $cantidad = (float)($cantidades[$i] ?? 0);
            if ($idProducto <= 0 || $cantidad <= 0) continue;
            $agrupados[$idProducto] = ($agrupados[$idProducto] ?? 0) + $cantidad;
        }

        $detalles = [];
        foreach ($agrupados as $idProducto => $cantidad) {
            $detalles[] = ['idProducto' => $idProducto, 'cantidad' => $cantidad];
        }

        $error = null;
        if (!$idAlmacen) {
            $error = 'Selecciona el almacén donde se realiza la venta.';
        } elseif (empty($detalles)) {
            $error = 'Agrega al menos un producto con cantidad mayor a cero.';
        } else {
            $resultado = $this->ventaModel->crear($idAlmacen, $idUsuario, $detalles, $metodoPago, $montoRecibido);
            if ($resultado['exito']) {
                $idVenta = $resultado['idVenta'];
                Log::registrar(
                    $idUsuario,
                    'Registró venta',
                    'Venta ' . Venta::numeroComprobante($idVenta) . ' - Total: $' . number_format($resultado['total'], 2) . " - Pago: {$metodoPago}"
                );

                $_SESSION['exito'] = 'Venta registrada y inventario actualizado correctamente.';
                if (!empty($resultado['avisos'])) {
                    $_SESSION['avisos'] = $resultado['avisos'];
                }
                if (!empty($resultado['alertasStock'])) {
                    $nombres = array_map(fn($a) => $a['insumo'], $resultado['alertasStock']);
                    $_SESSION['avisos'] = array_merge(
                        $_SESSION['avisos'] ?? [],
                        ['Stock bajo tras la venta: ' . implode(', ', array_unique($nombres)) . '.']
                    );
                }

                header("Location: index.php?action=ventas.comprobante&id={$idVenta}&nueva=1");
                exit;
            }
            $error = $resultado['error'];
        }

        // Si hubo error se vuelve a mostrar el formulario conservando lo que el usuario escribió
        [$productos, $almacenes, $metodosPago] = $this->datosFormulario();
        $old = $_POST;
        require __DIR__ . '/../views/ventas/crear.php';
    }

    public function verDetalle(): void {
        $id = (int)($_GET['id'] ?? 0);
        $venta = $this->ventaModel->buscarPorId($id);
        if (!$venta) {
            $_SESSION['error'] = 'La venta solicitada no existe.';
            header("Location: index.php?action=ventas");
            exit;
        }
        $detalles = $this->ventaModel->detallesDeVenta($id);
        require __DIR__ . '/../views/ventas/detalle.php';
    }

    // Comprobante de venta imprimible
    public function comprobante(): void {
        $id = (int)($_GET['id'] ?? 0);
        $venta = $this->ventaModel->buscarPorId($id);
        if (!$venta) {
            $_SESSION['error'] = 'La venta solicitada no existe.';
            header("Location: index.php?action=ventas");
            exit;
        }
        $detalles = $this->ventaModel->detallesDeVenta($id);
        $numeroComprobante = Venta::numeroComprobante((int)$venta['idVenta']);
        require __DIR__ . '/../views/ventas/comprobante.php';
    }
}
