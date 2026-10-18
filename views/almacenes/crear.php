<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Nueva sucursal / almacén</h2>

<div class="panel">
    <form method="POST" action="index.php?action=almacenes.guardar" class="formulario">
        <label>Nombre</label>
        <input type="text" name="nombre" required>

        <label>Ubicación</label>
        <input type="text" name="ubicacion" required>

        <label>Descripción</label>
        <textarea name="descripcion"></textarea>

        <button type="submit">Guardar</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>