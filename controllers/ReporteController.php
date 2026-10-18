<?php

require_once __DIR__ . '/../models/Merma.php';
require_once __DIR__ . '/../models/Compra.php';
require_once __DIR__ . '/../models/Almacen.php';
require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../models/Venta.php';

class ReporteController {
    private Merma $mermaModel;
    private Compra $compraModel;
    private Almacen $almacenModel;
    private Proveedor $proveedorModel;

    public function __construct() {
        $this->mermaModel = new Merma();
        $this->compraModel = new Compra();
        $this->almacenModel = new Almacen();
        $this->proveedorModel = new Proveedor();
    }

    // REQ-36: Reporte de Mermas
    public function mermas(): void {
        $idAlmacen = isset($_GET['idAlmacen']) && $_GET['idAlmacen'] !== '' ? (int)$_GET['idAlmacen'] : null;
        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin = $_GET['fechaFin'] ?? null;

        $mermas = $this->mermaModel->obtenerReporte($idAlmacen, $fechaInicio, $fechaFin);
        $almacenes = $this->almacenModel->listarTodos();

        require_once __DIR__ . '/../views/reportes/mermas.php';
    }

    // REQ-49: Reporte de Compras por Proveedor
    public function comprasProveedor(): void {
        $idProveedor = isset($_GET['idProveedor']) && $_GET['idProveedor'] !== '' ? (int)$_GET['idProveedor'] : null;
        $fechaInicio = $_GET['fechaInicio'] ?? null;
        $fechaFin = $_GET['fechaFin'] ?? null;

        $compras = $this->compraModel->obtenerReportePorProveedor($idProveedor, $fechaInicio, $fechaFin);
        $proveedores = $this->proveedorModel->listarTodos();

        require_once __DIR__ . '/../views/reportes/compras_proveedor.php';
    }

    // REQ-43: Log de auditoría
    public function auditoria(): void {
        $logModel = new Log();
        $logs = $logModel->obtenerTodos();
        require_once __DIR__ . '/../views/reportes/auditoria.php';
    }

    // Lee y valida los filtros del reporte de ventas
    private function filtrosVentas(): array {
        $fechaInicio = Venta::normalizarFecha($_GET['fechaInicio'] ?? null);
        $fechaFin = Venta::normalizarFecha($_GET['fechaFin'] ?? null);
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

    // Reporte de Ventas
    public function ventas(): void {
        [$fechaInicio, $fechaFin, $metodoPago, $idAlmacen] = $this->filtrosVentas();

        $ventaModel = new Venta();
        $resumen = $ventaModel->reporteResumen($fechaInicio, $fechaFin, $metodoPago, $idAlmacen);
        $porMetodo = $ventaModel->reportePorMetodoPago($fechaInicio, $fechaFin, $metodoPago, $idAlmacen);
        $porDia = $ventaModel->reportePorDia($fechaInicio, $fechaFin, $metodoPago, $idAlmacen);
        $topProductos = $ventaModel->reporteProductosMasVendidos($fechaInicio, $fechaFin, $metodoPago, $idAlmacen, 10);
        $almacenes = $this->almacenModel->listarTodos();
        $metodosPago = Venta::METODOS_PAGO;

        require_once __DIR__ . '/../views/reportes/ventas.php';
    }

    // Exportar reporte de ventas a Excel
    public function exportarVentasExcel(): void {
        [$fechaInicio, $fechaFin, $metodoPago, $idAlmacen] = $this->filtrosVentas();

        $ventaModel = new Venta();
        $resumen = $ventaModel->reporteResumen($fechaInicio, $fechaFin, $metodoPago, $idAlmacen);
        $ventas = $ventaModel->listarTodas($fechaInicio, $fechaFin, $metodoPago, $idAlmacen);
        $topProductos = $ventaModel->reporteProductosMasVendidos($fechaInicio, $fechaFin, $metodoPago, $idAlmacen, 10);

        $h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_ventas_' . date('Y-m-d') . '.xls"');

        echo '<meta charset="utf-8">';
        echo "<h3>Reporte de ventas</h3>";
        echo "<p>Periodo: " . $h($fechaInicio ?: 'inicio') . " a " . $h($fechaFin ?: 'hoy') . "</p>";

        echo "<table border='1'>";
        echo "<tr><th>N° ventas</th><th>Total vendido</th><th>Ticket promedio</th></tr>";
        echo "<tr><td>" . (int)$resumen['numVentas'] . "</td><td>" . number_format((float)$resumen['totalVendido'], 2, '.', '') . "</td><td>" . number_format((float)$resumen['ticketPromedio'], 2, '.', '') . "</td></tr>";
        echo "</table><br>";

        echo "<table border='1'>";
        echo "<tr><th>Comprobante</th><th>Fecha</th><th>Almacén</th><th>Vendedor</th><th>Método de pago</th><th>Total</th></tr>";
        foreach ($ventas as $v) {
            echo "<tr>";
            echo "<td>" . $h(Venta::numeroComprobante((int)$v['idVenta'])) . "</td>";
            echo "<td>" . $h($v['fechaVenta']) . "</td>";
            echo "<td>" . $h($v['nombreAlmacen']) . "</td>";
            echo "<td>" . $h($v['nombreUsuario']) . "</td>";
            echo "<td>" . $h($v['metodoPago']) . "</td>";
            echo "<td>" . number_format((float)$v['total'], 2, '.', '') . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";

        echo "<table border='1'>";
        echo "<tr><th>Producto más vendido</th><th>Unidades</th><th>Total vendido</th></tr>";
        foreach ($topProductos as $t) {
            echo "<tr><td>" . $h($t['nombreProducto']) . "</td><td>" . $h($t['unidadesVendidas']) . "</td><td>" . number_format((float)$t['totalVendido'], 2, '.', '') . "</td></tr>";
        }
        echo "</table>";
        exit;
    }

    // REQ-47: Exportar inventario a Excel
    public function exportarExcel(): void {
        $inventarioModel = new Inventario();
        $datos = $inventarioModel->listarCompleto();

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_inventario_' . date('Y-m-d') . '.xls"');

        echo "<table border='1'>";
        echo "<tr><th>Almacén</th><th>Insumo</th><th>Unidad</th><th>Disponible</th><th>Stock Mínimo</th><th>Estado</th></tr>";
        foreach ($datos as $d) {
            $estado = $d['cantidadDisponible'] <= $d['stockMinimo'] ? 'BAJO' : 'Normal';
            echo "<tr>";
            echo "<td>{$d['almacen_nombre']}</td>";
            echo "<td>{$d['insumo_nombre']}</td>";
            echo "<td>{$d['unidadMedida']}</td>";
            echo "<td>{$d['cantidadDisponible']}</td>";
            echo "<td>{$d['stockMinimo']}</td>";
            echo "<td>{$estado}</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit;
    }
}