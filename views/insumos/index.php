<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Insumos</h2>

<a href="index.php?action=insumos.crear" class="btn">+ Nuevo insumo</a>

<div class="panel">
    <table>
        <tr>
            <th>Nombre</th><th>Categoría</th><th>Unidad</th><th>Precio ref.</th><th>Estado</th><th>Acciones</th>
        </tr>
        <?php foreach ($insumos as $i): ?>
        <tr>
            <td><?= htmlspecialchars($i['nombre']) ?></td>
            <td><?= htmlspecialchars($i['nombreCategoria'] ?? 'Sin categoría') ?></td>
            <td><?= htmlspecialchars($i['unidadMedida']) ?></td>
            <td>$<?= number_format($i['precioUnitario'], 2) ?></td>
            <td><?= $i['estado'] ? 'Activo' : 'Inactivo' ?></td>
            <td>
                <a href="index.php?action=insumos.editar&id=<?= $i['idInsumo'] ?>">Editar</a>
                <?php if ($i['estado']): ?>
                    | <a href="index.php?action=insumos.desactivar&id=<?= $i['idInsumo'] ?>" class="btn-danger">Desactivar</a>
                <?php else: ?>
                    | <a href="index.php?action=insumos.activar&id=<?= $i['idInsumo'] ?>" class="btn-success">Activar</a>
                    | <form method="POST" action="index.php?action=insumos.eliminar" class="form-eliminar"
                            onsubmit="return confirm('¿Eliminar definitivamente? Esta acción no se puede deshacer.');">
                        <input type="hidden" name="id" value="<?= (int)$i['idInsumo'] ?>">
                        <button type="submit" class="btn-eliminar">Eliminar</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>