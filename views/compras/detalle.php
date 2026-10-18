<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Detalle de compra</title></head>
<body>
    <h2>Compra #<?= $compra['idCompra'] ?></h2>
    <p>Proveedor: <?= htmlspecialchars($compra['nombreProveedor']) ?></p>
    <p>Almacén: <?= htmlspecialchars($compra['nombreAlmacen']) ?></p>
    <p>Fecha: <?= htmlspecialchars($compra['fechaCompra']) ?></p>

    <table border="1">
        <tr><th>Insumo</th><th>Cantidad</th><th>Unidad</th><th>Precio unit.</th><th>Subtotal</th></tr>
        <?php foreach ($detalles as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['nombreInsumo']) ?></td>
            <td><?= $d['cantidad'] ?></td>
            <td><?= htmlspecialchars($d['unidadMedida']) ?></td>
            <td>$<?= number_format($d['precioUnitario'], 2) ?></td>
            <td>$<?= number_format($d['subtotal'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>Total: $<?= number_format($compra['total'], 2) ?></h3>
</body>
</html>