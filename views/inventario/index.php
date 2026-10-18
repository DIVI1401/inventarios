<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Inventario por sucursal</h2>

<div class="panel">
    <form method="GET" action="index.php" class="formulario">
        <input type="hidden" name="action" value="inventario">
        <label>Selecciona una sucursal / almacén</label>
        <select name="idAlmacen" onchange="this.form.submit()">
            <option value="">-- Selecciona --</option>
            <?php foreach ($almacenes as $a): ?>
                <option value="<?= $a['idAlmacen'] ?>" <?= $idAlmacenSeleccionado == $a['idAlmacen'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if ($idAlmacenSeleccionado): ?>
    <div class="panel">
        <?php if (empty($inventario)): ?>
            <p>Esta sucursal todavía no tiene insumos registrados en inventario.</p>
        <?php else: ?>
            <table>
                <tr><th>Insumo</th><th>Disponible</th><th>Stock mínimo</th><th>Estado</th></tr>
                <?php foreach ($inventario as $item): ?>
                    <?php 
                        $cantidad = $item['cantidadDisponible'] ?? 0;
                        $stockMinimo = $item['stockMinimo'] ?? 0;
                        $esBajo = $cantidad <= $stockMinimo;
                    ?>
                    <tr class="<?= $esBajo ? 'stock-bajo-fila' : '' ?>">
                        <td><?= htmlspecialchars($item['insumo_nombre'] ?? '') ?></td>
                        <td><?= htmlspecialchars($cantidad) ?> <?= htmlspecialchars($item['unidadMedida'] ?? '') ?></td>
                        <td><?= htmlspecialchars($stockMinimo) ?></td>
                        <td><?= $esBajo ? '⚠️ Bajo' : '✅ Normal' ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>