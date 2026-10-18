<?php
require_once __DIR__ . '/../layouts/header.php';
$qs = http_build_query(array_filter([
    'fechaInicio' => $fechaInicio,
    'fechaFin' => $fechaFin,
    'metodoPago' => $metodoPago,
    'idAlmacen' => $idAlmacen,
], fn($v) => $v !== null && $v !== ''));
?>

<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-graph-up-arrow me-2"></i>Reporte de Ventas</h2>
        <div class="d-flex gap-2">
            <a href="index.php?action=reportes.ventasExcel<?= $qs ? '&' . $qs : '' ?>" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Exportar Excel
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Imprimir / PDF
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="action" value="reportes.ventas">

                <div class="col-md-3">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date" name="fechaInicio" class="form-control" value="<?= htmlspecialchars($fechaInicio ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha fin</label>
                    <input type="date" name="fechaFin" class="form-control" value="<?= htmlspecialchars($fechaFin ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Método de pago</label>
                    <select name="metodoPago" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($metodosPago as $m): ?>
                            <option value="<?= htmlspecialchars($m) ?>" <?= $metodoPago === $m ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Almacén</label>
                    <select name="idAlmacen" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($almacenes as $a): ?>
                            <option value="<?= (int)$a['idAlmacen'] ?>" <?= ((int)$idAlmacen === (int)$a['idAlmacen']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                    <a href="index.php?action=reportes.ventas" class="btn btn-light border"><i class="bi bi-x-circle"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Indicadores -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-center h-100"><div class="card-body">
                <div class="text-muted">Ventas realizadas</div>
                <div class="fs-2 fw-bold"><?= (int)$resumen['numVentas'] ?></div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center h-100"><div class="card-body">
                <div class="text-muted">Total vendido</div>
                <div class="fs-2 fw-bold">$<?= number_format((float)$resumen['totalVendido'], 2) ?></div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card text-center h-100"><div class="card-body">
                <div class="text-muted">Ticket promedio</div>
                <div class="fs-2 fw-bold">$<?= number_format((float)$resumen['ticketPromedio'], 2) ?></div>
            </div></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">Ventas por método de pago</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead class="table-light"><tr><th>Método</th><th class="text-end">N° ventas</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                        <?php if (empty($porMetodo)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Sin datos para el periodo.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($porMetodo as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['metodoPago']) ?></td>
                                <td class="text-end"><?= (int)$r['numVentas'] ?></td>
                                <td class="text-end">$<?= number_format((float)$r['totalVendido'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">Productos más vendidos (Top 10)</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead class="table-light"><tr><th>Producto</th><th class="text-end">Unidades</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                        <?php if (empty($topProductos)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Sin datos para el periodo.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($topProductos as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['nombreProducto']) ?></td>
                                <td class="text-end"><?= rtrim(rtrim(number_format((float)$r['unidadesVendidas'], 2, '.', ''), '0'), '.') ?></td>
                                <td class="text-end">$<?= number_format((float)$r['totalVendido'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header">Ventas por día</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead class="table-light"><tr><th>Fecha</th><th class="text-end">N° ventas</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                        <?php if (empty($porDia)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Sin datos para el periodo.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($porDia as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($r['dia']))) ?></td>
                                <td class="text-end"><?= (int)$r['numVentas'] ?></td>
                                <td class="text-end">$<?= number_format((float)$r['totalVendido'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
