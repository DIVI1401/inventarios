<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Compras</h2>

<a href="index.php?action=compras.crear" class="btn">+ Nueva compra</a>

<div class="panel">
    <table>
        <tr><th>Fecha</th><th>Proveedor</th><th>Almacén</th><th>Total</th><th></th></tr>
        <?php foreach ($compras as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['fechaCompra']) ?></td>
            <td><?= htmlspecialchars($c['nombreProveedor']) ?></td>
            <td><?= htmlspecialchars($c['nombreAlmacen']) ?></td>
            <td>$<?= number_format($c['total'], 2) ?></td>
            <td><a href="index.php?action=compras.detalle&id=<?= $c['idCompra'] ?>">Ver detalle</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>