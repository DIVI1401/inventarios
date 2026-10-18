<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Editar proveedor</h2>

<div class="panel">
    <?php if (!empty($_SESSION['error'])): ?>
    <p class="error-msg"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
    <form method="POST" action="index.php?action=proveedores.actualizar" class="formulario">
        <input type="hidden" name="idProveedor" value="<?= $proveedor['idProveedor'] ?>">

        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($proveedor['nombre']) ?>" required>

        <label>Contacto</label>
        <input type="text" name="contacto" value="<?= htmlspecialchars($proveedor['contacto']) ?>">

        <label>Teléfono</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($proveedor['telefono']) ?>">

        <label>Correo</label>
        <input type="email" name="correo" value="<?= htmlspecialchars($proveedor['correo']) ?>">

        <label>Dirección</label>
        <input type="text" name="direccion" value="<?= htmlspecialchars($proveedor['direccion']) ?>">

        <button type="submit">Actualizar</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>