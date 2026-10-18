<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Bienvenido, <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?></h2>

<div class="indicadores">
    <div class="tarjeta">
        <h3>Gasto en compras (este mes)</h3>
        <p class="numero">$<?= number_format($gastoDelMes, 2) ?></p>
    </div>

    <div class="tarjeta <?= count($stockBajo) > 0 ? 'alerta' : '' ?>">
        <h3>Insumos con stock bajo</h3>
        <p class="numero"><?= count($stockBajo) ?></p>
    </div>

    <div class="tarjeta <?= count($proximosAVencer) > 0 ? 'alerta' : '' ?>">
        <h3>Próximos a vencer (7 días)</h3>
        <p class="numero"><?= count($proximosAVencer) ?></p>
    </div>
</div>

<div class="panel">
    <h3>⚠️ Insumos con stock bajo</h3>
    <?php if (empty($stockBajo)): ?>
        <p>No hay insumos con stock bajo.</p>
    <?php else: ?>
        <table>
            <tr><th>Insumo</th><th>Almacén</th><th>Disponible</th><th>Mínimo</th></tr>
            <?php foreach ($stockBajo as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['nombreInsumo']) ?></td>
                <td><?= htmlspecialchars($s['nombreAlmacen']) ?></td>
                <td><?= $s['cantidadDisponible'] ?> <?= htmlspecialchars($s['unidadMedida']) ?></td>
                <td><?= $s['stockMinimo'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<div class="panel">
    <h3>⏰ Insumos próximos a vencer</h3>
    <?php if (empty($proximosAVencer)): ?>
        <p>No hay insumos próximos a vencer.</p>
    <?php else: ?>
        <table>
            <tr><th>Insumo</th><th>Fecha de vencimiento</th></tr>
            <?php foreach ($proximosAVencer as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['fechaVencimiento']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>