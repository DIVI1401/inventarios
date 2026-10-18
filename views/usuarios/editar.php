<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Editar usuario</h2>

<div class="panel">
    <form method="POST" action="index.php?action=usuarios.actualizar" class="formulario">
        <input type="hidden" name="idUsuario" value="<?= $usuario['idUsuario'] ?>">

        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

        <label>Correo</label>
        <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>

        <label>Rol</label>
        <select name="idRol" required>
            <?php foreach ($roles as $r): ?>
                <option value="<?= $r['idRol'] ?>" <?= $r['idRol'] == $usuario['idRol'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Actualizar</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>