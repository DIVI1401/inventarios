<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Nuevo proveedor</h2>

<div class="panel">
    <?php if (!empty($_SESSION['error'])): ?>
    <p class="error-msg"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
    <form method="POST" action="index.php?action=proveedores.guardar" class="formulario">
        <label>Nombre</label>
        <input type="text" name="nombre" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios" required>

        <label>Contacto</label>
        <input type="text" name="contacto" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo letras y espacios">

        <label>Teléfono</label>
        <input type="tel" name="telefono" pattern="[0-9]{7,15}" title="Solo números, entre 7 y 15 dígitos" maxlength="15">

        <label>Correo</label>
        <input type="email" name="correo">

        <label>Dirección</label>
        <input type="text" name="direccion" maxlength="150">
        <button type="submit">Guardar</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>