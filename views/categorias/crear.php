<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Nueva categoría</h2>

<?php if (!empty($_SESSION['error'])): ?>
    <p class="error-msg"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="panel">
    <form method="POST" action="index.php?action=categorias.guardar" class="formulario">
        <label>Nombre</label>
        <input type="text" name="nombre" maxlength="50" required>

        <label>Descripción</label>
        <textarea name="descripcion" maxlength="150"></textarea>

        <button type="submit">Guardar</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>