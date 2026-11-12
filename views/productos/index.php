<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Productos del Restaurante</h2>
    <a href="index.php?action=productos.crear" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nuevo Producto
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Costo receta</th>
                        <th>Margen</th>
                        <th>Precio venta</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">No hay productos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($productos as $p): ?>
                            <tr>
                                <td><?= $p['idProducto'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($p['nombre']) ?></strong>
                                    <?php if (!empty($p['descripcion'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($p['descripcion']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($p['categoria'] ?? 'Sin categoría') ?></td>
                                <td><?= $p['costoTotal'] > 0 ? '$' . number_format($p['costoTotal'], 2) : '<span class="text-muted">Sin receta</span>' ?></td>
                                <td><?= (float)$p['margenGanancia'] ?> %</td>
                                <td><?= $p['costoTotal'] > 0 ? '<strong>$' . number_format($p['precio'], 2) . '</strong>' : '<span class="text-muted">—</span>' ?></td>
                                <td>
                                    <span class="badge bg-<?= $p['estado'] ? 'success' : 'danger' ?>">
                                        <?= $p['estado'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="index.php?action=productos.receta&id=<?= $p['idProducto'] ?>" class="btn btn-sm btn-outline-info" title="Gestionar Receta">
                                        <i class="bi bi-journal-text me-1"></i>Receta
                                    </a>
                                    <a href="index.php?action=productos.editar&id=<?= $p['idProducto'] ?>" class="btn btn-sm btn-outline-primary ms-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="index.php?action=productos.estado&id=<?= $p['idProducto'] ?>&estado=<?= $p['estado'] ? 0 : 1 ?>" 
                                       class="btn btn-sm btn-outline-<?= $p['estado'] ? 'warning' : 'success' ?> ms-1"
                                       title="<?= $p['estado'] ? 'Desactivar' : 'Activar' ?>"
                                       onclick="return confirm('¿Desea cambiar el estado de este producto?');">
                                        <i class="bi bi-power"></i>
                                    </a>
                                    <?php if (!$p['estado']): ?>
                                        <form method="POST" action="index.php?action=productos.eliminar" class="d-inline"
                                              onsubmit="return confirm('¿Eliminar definitivamente este producto y su receta? Esta acción no se puede deshacer.');">
                                            <input type="hidden" name="id" value="<?= (int)$p['idProducto'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger ms-1" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>