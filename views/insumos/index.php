<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Insumos</h2>

<a href="index.php?action=insumos.crear" class="btn">+ Nuevo insumo</a>

<div class="panel">
    <table>
        <tr>
            <th>Nombre</th><th>Categoría</th><th>Unidad</th><th>Precio</th><th>Vencimiento</th><th>Estado</th><th>Acciones</th>
        </tr>
        <?php foreach ($insumos as $i): ?>
        <tr class="<?= (!empty($i['fechaVencimiento']) && strtotime($i['fechaVencimiento']) <= strtotime('+7 days')) ? 'stock-bajo-fila' : '' ?>">
            <td><?= htmlspecialchars($i['nombre']) ?></td>
            <td><?= htmlspecialchars($i['nombreCategoria'] ?? 'Sin categoría') ?></td>
            <td><?= htmlspecialchars($i['unidadMedida']) ?></td>
            <td>$<?= number_format($i['precioUnitario'], 2) ?></td>
            <td><?= htmlspecialchars($i['fechaVencimiento'] ?? 'N/A') ?></td>
            <td><?= $i['estado'] ? 'Activo' : 'Inactivo' ?></td>
            <td>
                <a href="index.php?action=insumos.editar&id=<?= $i['idInsumo'] ?>">Editar</a>
                <?php if ($i['estado']): ?>
                    | <a href="index.php?action=insumos.desactivar&id=<?= $i['idInsumo'] ?>" class="btn-danger">Desactivar</a>
                <?php else: ?>
                    | <a href="index.php?action=insumos.activar&id=<?= $i['idInsumo'] ?>" class="btn-success">Activar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>