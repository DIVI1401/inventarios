<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Compra #<?= (int)$compra['idCompra'] ?></h2>

<div class="panel">
    <p><strong>Proveedor:</strong> <?= htmlspecialchars($compra['nombreProveedor']) ?></p>
    <p><strong>Almacén:</strong> <?= htmlspecialchars($compra['nombreAlmacen']) ?></p>
    <p><strong>Fecha:</strong> <?= htmlspecialchars($compra['fechaCompra']) ?></p>

    <table>
        <tr><th>Insumo</th><th>Cantidad</th><th>Unidad</th><th>Precio unit.</th><th>Subtotal</th><th>Vence</th></tr>
        <?php foreach ($detalles as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['nombreInsumo']) ?></td>
            <td><?= (float)$d['cantidad'] ?></td>
            <td><?= htmlspecialchars($d['unidadMedida']) ?></td>
            <td>$<?= number_format($d['precioUnitario'], 2) ?></td>
            <td>$<?= number_format($d['subtotal'], 2) ?></td>
            <td><?= htmlspecialchars($d['fechaVencimiento'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3 style="margin-top:15px;">Total: $<?= number_format($compra['total'], 2) ?></h3>
    <a href="index.php?action=compras" class="btn">← Volver a compras</a>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
