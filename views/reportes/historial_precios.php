<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Historial de Precios por Insumo</h2>

<div class="panel">
    <form method="GET" action="index.php" class="formulario">
        <input type="hidden" name="action" value="precios.historial">
        <label>Selecciona un insumo</label>
        <select name="idInsumo" onchange="this.form.submit()">
            <option value="">-- Selecciona --</option>
            <?php foreach ($insumos as $i): ?>
                <option value="<?= $i['idInsumo'] ?>" <?= $idInsumo == $i['idInsumo'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($i['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if ($idInsumo): ?>
<div class="panel">
    <table>
        <tr><th>Fecha</th><th>Proveedor</th><th>Cantidad</th><th>Precio unitario</th></tr>
        <?php if (empty($historial)): ?>
            <tr><td colspan="4" style="text-align:center;">Este insumo aún no tiene compras registradas.</td></tr>
        <?php else: ?>
            <?php foreach ($historial as $h): ?>
            <tr>
                <td><?= htmlspecialchars($h['fechaCompra']) ?></td>
                <td><?= htmlspecialchars($h['nombreProveedor']) ?></td>
                <td><?= $h['cantidad'] ?></td>
                <td>$<?= number_format($h['precioUnitario'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>