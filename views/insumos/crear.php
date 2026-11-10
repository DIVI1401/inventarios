<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Nuevo insumo</h2>

<?php if (!empty($_SESSION['error'])): ?>
    <p class="error-msg"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="panel">
    <form method="POST" action="index.php?action=insumos.guardar" class="formulario">
        <label>Nombre</label>
        <input type="text" name="nombre" required>

        <label>Unidad de medida</label>
        <select name="unidadMedida" required>
            <option value="kg">Kilogramos (kg)</option>
            <option value="litros">Litros</option>
            <option value="unidades">Unidades</option>
            <option value="gramos">Gramos</option>
            <option value="ml">Mililitros</option>
        </select>

        <label>Precio de referencia (por unidad de medida)</label>
        <input type="number" step="0.01" min="0.01" name="precioUnitario" required>

        <label>Stock Mínimo (Umbral de Alerta)</label>
        <input type="number" step="0.01" name="stockMinimo" min="0" value="5" required>
        <small style="color:#666;display:block;margin-bottom:12px;">La fecha de vencimiento se registra en cada compra, porque cada lote vence en una fecha distinta.</small>

        <label>Categoría</label>
        <select name="idCategoria">
            <option value="">-- Sin categoría --</option>
            <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['idCategoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Guardar</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>