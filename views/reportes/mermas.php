<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-file-earmark-bar-graph me-2"></i>Reporte de Mermas</h2>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Imprimir / PDF
        </button>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-3">
                <input type="hidden" name="action" value="reportes.mermas">

                <div class="col-md-4">
                    <label class="form-label font-weight-bold">Almacén</label>
                    <select name="idAlmacen" class="form-select">
                        <option value="">-- Todos los almacenes --</option>
                        <?php foreach ($almacenes as $a): ?>
                            <option value="<?= $a['idAlmacen'] ?>" <?= (isset($_GET['idAlmacen']) && $_GET['idAlmacen'] == $a['idAlmacen']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['nombre']) ?>
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
                    <a href="index.php?action=reportes.mermas" class="btn btn-light border">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Mermas -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Insumo</th>
                            <th>Almacén</th>
                            <th>Cantidad</th>
                            <th>Motivo</th>
                            <th>Registrado Por</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mermas)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No se encontraron registros de mermas para los filtros seleccionados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($mermas as $m): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($m['fechaMerma'])) ?></td>
                                    <td><strong><?= htmlspecialchars($m['nombreInsumo']) ?></strong></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($m['nombreAlmacen']) ?></span></td>
                                    <td class="text-danger fw-bold">-<?= number_format($m['cantidad'], 2) ?> <?= htmlspecialchars($m['unidadMedida'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($m['motivo']) ?></td>
                                    <td><?= htmlspecialchars($m['nombreUsuario']) ?></td>
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