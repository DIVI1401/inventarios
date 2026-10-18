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
                        <th>Precio</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No hay productos registrados.</td></tr>
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
                                <td>$<?= number_format($p['precio'], 2) ?></td>
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
                                       onclick="return confirm('¿Desea cambiar el estado de este producto?');">
                                        <i class="bi bi-power"></i>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>