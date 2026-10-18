<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Usuarios</h2>

<a href="index.php?action=usuarios.crear" class="btn">+ Nuevo usuario</a>

<div class="panel">
    <table>
        <tr>
            <th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th>
        </tr>
        <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['nombre']) ?></td>
            <td><?= htmlspecialchars($u['correo']) ?></td>
            <td><?= htmlspecialchars($u['nombreRol']) ?></td>
            <td><?= $u['estado'] ? 'Activo' : 'Inactivo' ?></td>
            <td>
                <a href="index.php?action=usuarios.editar&id=<?= $u['idUsuario'] ?>">Editar</a>
                <?php if ($u['estado']): ?>
                    | <a href="index.php?action=usuarios.desactivar&id=<?= $u['idUsuario'] ?>" class="btn-danger">Desactivar</a>
                <?php else: ?>
                    | <a href="index.php?action=usuarios.activar&id=<?= $u['idUsuario'] ?>" class="btn-success">Activar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>