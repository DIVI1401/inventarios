<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2>Alertas de Stock Mínimo y Sugerencias de Compra</h2>

<table style="width:100%; border-collapse: collapse; margin-top: 15px;" border="1">
    <thead>
        <tr>
            <th>Insumo</th>
            <th>Almacén</th>
            <th>Stock Actual</th>
            <th>Stock Mínimo</th>
            <th>Sugerencia de Compra</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($sugerencias)): ?>
            <tr>
                <td colspan="5" style="text-align: center;">No hay alertas de stock bajo actualmente.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($sugerencias as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['insumo_nombre']) ?></td>
                    <td><?= htmlspecialchars($row['almacen_nombre']) ?></td>
                    <td style="color: red; font-weight: bold;"><?= $row['stock_actual'] ?> <?= htmlspecialchars($row['unidadMedida']) ?></td>
                    <td><?= $row['stock_minimo'] ?> <?= htmlspecialchars($row['unidadMedida']) ?></td>
                    <td><strong><?= $row['sugerencia_compra'] ?></strong> <?= htmlspecialchars($row['unidadMedida']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($sugerencias)): ?>
<form method="POST" action="index.php?action=inventario.notificarAhora" style="margin-top:20px;">
    <button type="submit" class="btn">📧 Enviar notificación de estas alertas ahora</button>
</form>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>