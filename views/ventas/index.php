<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-receipt me-2"></i>Historial de ventas</h2>
        <a href="index.php?action=ventas.crear" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nueva venta
        </a>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Filtros / búsqueda por fecha -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="action" value="ventas">

                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fechaInicio" class="form-control" value="<?= htmlspecialchars($fechaInicio ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fechaFin" class="form-control" value="<?= htmlspecialchars($fechaFin ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Método de pago</label>
                    <select name="metodoPago" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($metodosPago as $m): ?>
                            <option value="<?= htmlspecialchars($m) ?>" <?= ($metodoPago === $m) ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
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
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Buscar</button>
                    <a href="index.php?action=ventas" class="btn btn-light border" title="Limpiar filtros"><i class="bi bi-x-circle"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Comprobante</th>
                            <th>Fecha</th>
                            <th>Almacén</th>
                            <th>Vendedor</th>
                            <th>Pago</th>
                            <th class="text-end">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ventas)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No se encontraron ventas con esos criterios.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($ventas as $v): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars(Venta::numeroComprobante((int)$v['idVenta'])) ?></strong></td>
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($v['fechaVenta']))) ?></td>
                            <td><?= htmlspecialchars($v['nombreAlmacen']) ?></td>
                            <td><?= htmlspecialchars($v['nombreUsuario']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($v['metodoPago']) ?></span></td>
                            <td class="text-end">$<?= number_format($v['total'], 2) ?></td>
                            <td class="text-end text-nowrap">
                                <a href="index.php?action=ventas.detalle&id=<?= (int)$v['idVenta'] ?>" class="btn btn-sm btn-outline-secondary">Detalle</a>
                                <a href="index.php?action=ventas.comprobante&id=<?= (int)$v['idVenta'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="bi bi-printer"></i> Comprobante
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php if (!empty($ventas)): ?>
                    <tfoot>
                        <tr class="table-light">
                            <th colspan="5"><?= count($ventas) ?> venta(s) encontradas</th>
                            <th class="text-end">$<?= number_format($totalFiltrado, 2) ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
