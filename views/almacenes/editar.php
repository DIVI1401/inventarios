<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Editar sucursal / almacén</h2>

<div class="panel">
    <form method="POST" action="index.php?action=almacenes.actualizar" class="formulario">
        <input type="hidden" name="idAlmacen" value="<?= $almacen['idAlmacen'] ?>">

        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($almacen['nombre']) ?>" required>

        <label>Ubicación</label>
        <input type="text" name="ubicacion" value="<?= htmlspecialchars($almacen['ubicacion']) ?>" required>

        <label>Descripción</label>
        <textarea name="descripcion"><?= htmlspecialchars($almacen['descripcion']) ?></textarea>

        <button type="submit">Actualizar</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>