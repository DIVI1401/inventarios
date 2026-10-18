<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Historial de Transferencias</h2>

<div class="panel">
    <table>
        <tr><th>Fecha</th><th>Insumo</th><th>Origen</th><th>Destino</th><th>Cantidad</th><th>Realizado por</th></tr>
        <?php if (empty($movimientos)): ?>
            <tr><td colspan="6" style="text-align:center;">Aún no se han hecho transferencias.</td></tr>
        <?php else: ?>
            <?php foreach ($movimientos as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['fecha']) ?></td>
                <td><?= htmlspecialchars($m['insumo_nombre']) ?></td>
                <td><?= htmlspecialchars($m['almacen_origen']) ?></td>
                <td><?= htmlspecialchars($m['almacen_destino']) ?></td>
                <td><?= $m['cantidad'] ?> <?= htmlspecialchars($m['unidadMedida']) ?></td>
                <td><?= htmlspecialchars($m['usuario_nombre']) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>