<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Editar categoría</h2>

<div class="panel">
    <form method="POST" action="index.php?action=categorias.actualizar" class="formulario">
        <input type="hidden" name="idCategoria" value="<?= $categoria['idCategoria'] ?>">

        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($categoria['nombre']) ?>" maxlength="50" required>

        <label>Descripción</label>
        <textarea name="descripcion" maxlength="150"><?= htmlspecialchars($categoria['descripcion']) ?></textarea>

        <button type="submit">Actualizar</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>