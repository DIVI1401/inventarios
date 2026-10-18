<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Proveedores</h2>

<a href="index.php?action=proveedores.crear" class="btn">+ Nuevo proveedor</a>

<div class="panel">
    <table>
        <tr><th>Nombre</th><th>Contacto</th><th>Teléfono</th><th>Correo</th><th>Estado</th><th>Acciones</th></tr>
        <?php foreach ($proveedores as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['nombre']) ?></td>
            <td><?= htmlspecialchars($p['contacto']) ?></td>
            <td><?= htmlspecialchars($p['telefono']) ?></td>
            <td><?= htmlspecialchars($p['correo']) ?></td>
            <td><?= $p['estado'] ? 'Activo' : 'Inactivo' ?></td>
            <td>
                <a href="index.php?action=proveedores.editar&id=<?= $p['idProveedor'] ?>">Editar</a>
                <?php if ($p['estado']): ?>
                    | <a href="index.php?action=proveedores.desactivar&id=<?= $p['idProveedor'] ?>" class="btn-danger">Desactivar</a>
                <?php else: ?>
                    | <a href="index.php?action=proveedores.activar&id=<?= $p['idProveedor'] ?>" class="btn-success">Activar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>