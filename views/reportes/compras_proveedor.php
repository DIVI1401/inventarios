<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-cart-check me-2"></i>Reporte de Compras por Proveedor</h2>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Imprimir / PDF
        </button>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="action" value="reportes.comprasProveedor">

                <div class="col-md-4">
                    <label class="form-label font-weight-bold">Proveedor</label>
                    <select name="idProveedor" class="form-select">
                        <option value="">-- Todos los proveedores --</option>
                        <?php foreach ($proveedores as $p): ?>
                            <option value="<?= $p['idProveedor'] ?>" <?= (isset($_GET['idProveedor']) && $_GET['idProveedor'] == $p['idProveedor']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fechaInicio" class="form-control" value="<?= htmlspecialchars($_GET['fechaInicio'] ?? '') ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fechaFin" class="form-control" value="<?= htmlspecialchars($_GET['fechaFin'] ?? '') ?>">
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Filtrar
                    </button>
                    <a href="index.php?action=reportes.comprasProveedor" class="btn btn-light border">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Compras -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th># Orden</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Almacén Destino</th>
                            <th>Ítems</th>
                            <th>Total ($)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($compras)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No se encontraron compras para los filtros seleccionados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($compras as $c): ?>
                                <tr>
                                    <td>#<?= $c['idCompra'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($c['fechaCompra'])) ?></td>
                                    <td><strong><?= htmlspecialchars($c['nombreProveedor']) ?></strong></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($c['nombreAlmacen']) ?></span></td>
                                    <td><?= $c['totalItems'] ?> prod.</td>
                                    <td class="text-success fw-bold">$<?= number_format($c['total'], 2) ?></td>
                                    <td>
                                        <a href="index.php?action=compras.detalle&id=<?= $c['idCompra'] ?>" class="btn btn-sm btn-outline-info">
                                            <i class="bi bi-eye"></i> Detalle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>