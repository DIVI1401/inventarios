<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Stock Disponible por Producto</h2>

<div class="panel">
    <form method="GET" action="index.php" class="formulario">
        <input type="hidden" name="action" value="productos.stock">
        <label>Selecciona una sucursal / almacén</label>
        <select name="idAlmacen" onchange="this.form.submit()">
            <option value="">-- Selecciona --</option>
            <?php foreach ($almacenes as $a): ?>
                <option value="<?= $a['idAlmacen'] ?>" <?= $idAlmacen == $a['idAlmacen'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if ($idAlmacen): ?>
<div class="panel">
    <table>
        <tr><th>Producto</th><th>Precio</th><th>Unidades que se pueden preparar</th></tr>
        <?php foreach ($productos as $p): ?>
            <?php $stock = $stockPorProducto[$p['idProducto']] ?? null; ?>
            <tr class="<?= ($stock !== null && $stock == 0) ? 'stock-bajo-fila' : '' ?>">
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td>$<?= number_format($p['precio'], 2) ?></td>
                <td>
                    <?= $stock === null ? '<em>Sin receta asignada</em>' : $stock . ' unidades' ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>