<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Sucursales / Almacenes</h2>

<a href="index.php?action=almacenes.crear" class="btn">+ Nueva sucursal</a>

<div class="panel">
    <table>
        <tr>
            <th>Nombre</th><th>Ubicación</th><th>Descripción</th><th>Estado</th><th>Acciones</th>
        </tr>
        <?php foreach ($almacenes as $a): ?>
        <tr>
            <td><?= htmlspecialchars($a['nombre']) ?></td>
            <td><?= htmlspecialchars($a['ubicacion']) ?></td>
            <td><?= htmlspecialchars($a['descripcion']) ?></td>
            <td><?= $a['estado'] ? 'Activo' : 'Inactivo' ?></td>
            <td>
                <a href="index.php?action=almacenes.editar&id=<?= $a['idAlmacen'] ?>">Editar</a>
                <?php if ($a['estado']): ?>
                    | <a href="index.php?action=almacenes.desactivar&id=<?= $a['idAlmacen'] ?>" class="btn-danger">Desactivar</a>
                <?php else: ?>
                    | <a href="index.php?action=almacenes.activar&id=<?= $a['idAlmacen'] ?>" class="btn-success">Activar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>