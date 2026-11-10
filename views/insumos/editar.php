<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Editar insumo</h2>

<?php if (!empty($_SESSION['error'])): ?>
    <p class="error-msg"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="panel">
    <form method="POST" action="index.php?action=insumos.actualizar" class="formulario">
        <input type="hidden" name="idInsumo" value="<?= $insumo['idInsumo'] ?>">

        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($insumo['nombre']) ?>" required>

        <label>Unidad de medida</label>
        <select name="unidadMedida" required>
            <?php foreach (['kg', 'litros', 'unidades', 'gramos', 'ml'] as $u): ?>
                <option value="<?= $u ?>" <?= $u === $insumo['unidadMedida'] ? 'selected' : '' ?>><?= $u ?></option>
            <?php endforeach; ?>
        </select>

        <label>Precio de referencia (por unidad de medida)</label>
        <input type="number" step="0.01" min="0.01" name="precioUnitario" value="<?= $insumo['precioUnitario'] ?>" required>

        <label>Stock Mínimo (Umbral de Alerta)</label>
        <input type="number" step="0.01" min="0" name="stockMinimo" value="<?= (float)$insumo['stockMinimo'] ?>" required>
        <label>Categoría</label>
        <select name="idCategoria">
            <option value="">-- Sin categoría --</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= $c['idCategoria'] ?>" <?= $c['idCategoria'] == $insumo['idCategoria'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['nombre']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Actualizar</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>