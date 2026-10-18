<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Categorías de Insumos</h2>
<a href="index.php?action=categorias.crear" class="btn">+ Nueva categoría</a>

<div class="panel">
    <table>
        <tr><th>Nombre</th><th>Descripción</th><th>Estado</th><th>Acciones</th></tr>
        <?php foreach ($categorias as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['nombre']) ?></td>
            <td><?= htmlspecialchars($c['descripcion']) ?></td>
            <td><?= $c['estado'] ? 'Activo' : 'Inactivo' ?></td>
            <td>
                <a href="index.php?action=categorias.editar&id=<?= $c['idCategoria'] ?>">Editar</a>
                <?php if ($c['estado']): ?>
                    | <a href="index.php?action=categorias.desactivar&id=<?= $c['idCategoria'] ?>" class="btn-danger">Desactivar</a>
                <?php else: ?>
                    | <a href="index.php?action=categorias.activar&id=<?= $c['idCategoria'] ?>" class="btn-success">Activar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>