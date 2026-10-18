<?php
$cantidadFmt = fn($n) => rtrim(rtrim(number_format((float)$n, 2, '.', ''), '0'), '.');
$cambio = null;
if ($venta['metodoPago'] === 'Efectivo' && $venta['montoRecibido'] !== null) {
    $cambio = max(0, (float)$venta['montoRecibido'] - (float)$venta['total']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante <?= htmlspecialchars($numeroComprobante) ?> - AetherStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #f4f6f9; }
        .ticket { max-width: 480px; margin: 24px auto; background: #fff; padding: 28px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        .ticket h1 { font-size: 1.4rem; }
        .ticket .linea { border-top: 1px dashed #999; margin: 14px 0; }
        @media print {
            body { background: #fff; }
            .ticket { box-shadow: none; margin: 0 auto; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="no-print mt-3" style="max-width:480px;margin:auto;">
        <?php if (!empty($_SESSION['exito'])): ?>
            <div class="alert alert-success mb-2"><?= htmlspecialchars($_SESSION['exito']) ?></div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>
        <?php if (!empty($_SESSION['avisos'])): ?>
            <?php foreach ($_SESSION['avisos'] as $aviso): ?>
                <div class="alert alert-warning mb-2"><?= htmlspecialchars($aviso) ?></div>
            <?php endforeach; ?>
            <?php unset($_SESSION['avisos']); ?>
        <?php endif; ?>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-1"></i> Imprimir / Guardar PDF</button>
            <a href="index.php?action=ventas.crear" class="btn btn-success">Nueva venta</a>
            <a href="index.php?action=ventas" class="btn btn-outline-secondary">Historial</a>
        </div>
    </div>

    <div class="ticket">
        <div class="text-center">
            <h1 class="mb-0">AetherStock F&amp;B</h1>
            <div class="text-muted"><?= htmlspecialchars($venta['nombreAlmacen']) ?></div>
            <?php if (!empty($venta['ubicacionAlmacen'])): ?>
                <div class="text-muted small"><?= htmlspecialchars($venta['ubicacionAlmacen']) ?></div>
            <?php endif; ?>
            <div class="mt-2 fw-bold">COMPROBANTE DE VENTA</div>
            <div class="fs-5"><?= htmlspecialchars($numeroComprobante) ?></div>
        </div>

        <div class="linea"></div>

        <div class="small">
            <div><strong>Fecha:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($venta['fechaVenta']))) ?></div>
            <div><strong>Atendió:</strong> <?= htmlspecialchars($venta['nombreUsuario']) ?></div>
            <div><strong>Método de pago:</strong> <?= htmlspecialchars($venta['metodoPago']) ?></div>
        </div>

        <div class="linea"></div>

        <table class="table table-sm table-borderless mb-0">
            <thead>
                <tr class="border-bottom">
                    <th>Producto</th>
                    <th class="text-end">Cant.</th>
                    <th class="text-end">P. unit.</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['nombreProducto']) ?></td>
                    <td class="text-end"><?= $cantidadFmt($d['cantidad']) ?></td>
                    <td class="text-end">$<?= number_format($d['precioUnitario'], 2) ?></td>
                    <td class="text-end">$<?= number_format($d['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="linea"></div>

        <div class="d-flex justify-content-between fs-5 fw-bold">
            <span>TOTAL</span>
            <span>$<?= number_format($venta['total'], 2) ?></span>
        </div>
        <?php if ($venta['montoRecibido'] !== null): ?>
            <div class="d-flex justify-content-between small mt-1">
                <span>Recibido</span><span>$<?= number_format($venta['montoRecibido'], 2) ?></span>
            </div>
            <div class="d-flex justify-content-between small">
                <span>Cambio</span><span>$<?= number_format($cambio, 2) ?></span>
            </div>
        <?php endif; ?>

        <div class="linea"></div>
        <div class="text-center small text-muted">¡Gracias por su compra!</div>
    </div>
</div>
</body>
</html>
