<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-receipt me-2"></i>Venta <?= htmlspecialchars(Venta::numeroComprobante((int)$venta['idVenta'])) ?></h2>
        <div>
            <a href="index.php?action=ventas.comprobante&id=<?= (int)$venta['idVenta'] ?>" class="btn btn-primary" target="_blank">
                <i class="bi bi-printer me-1"></i> Ver comprobante
            </a>
            <a href="index.php?action=ventas" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="mb-1"><strong>Almacén:</strong> <?= htmlspecialchars($venta['nombreAlmacen']) ?></p>
            <p class="mb-1"><strong>Fecha:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($venta['fechaVenta']))) ?></p>
            <p class="mb-1"><strong>Vendedor:</strong> <?= htmlspecialchars($venta['nombreUsuario']) ?></p>
            <p class="mb-3"><strong>Método de pago:</strong> <?= htmlspecialchars($venta['metodoPago']) ?></p>

            <div class="table-responsive">
                <table class="table">
                    <thead class="table-light">
                        <tr><th>Producto</th><th class="text-end">Cantidad</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['nombreProducto']) ?></td>
                            <td class="text-end"><?= rtrim(rtrim(number_format($d['cantidad'], 2, '.', ''), '0'), '.') ?></td>
                            <td class="text-end">$<?= number_format($d['precioUnitario'], 2) ?></td>
                            <td class="text-end">$<?= number_format($d['subtotal'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <h4 class="text-end">Total: $<?= number_format($venta['total'], 2) ?></h4>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
