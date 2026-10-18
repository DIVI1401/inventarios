<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Comparar Precios entre Proveedores</h2>

<div class="panel">
    <form method="GET" action="index.php" class="formulario">
        <input type="hidden" name="action" value="precios.comparativa">
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
        <tr><th>Proveedor</th><th>Veces comprado</th><th>Precio promedio</th><th>Mínimo</th><th>Máximo</th><th>Última compra</th></tr>
        <?php if (empty($comparativa)): ?>
            <tr><td colspan="6" style="text-align:center;">Este insumo aún no tiene compras registradas.</td></tr>
        <?php else: ?>
            <?php foreach ($comparativa as $c): ?>
            <tr>
                <td><?= htmlspecialchars($c['nombreProveedor']) ?></td>
                <td><?= $c['vecesComprado'] ?></td>
                <td>$<?= number_format($c['precioPromedio'], 2) ?></td>
                <td>$<?= number_format($c['precioMinimo'], 2) ?></td>
                <td>$<?= number_format($c['precioMaximo'], 2) ?></td>
                <td><?= htmlspecialchars($c['ultimaCompra']) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>