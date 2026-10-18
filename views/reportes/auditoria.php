<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Log de Auditoría</h2>

<div class="panel">
    <table>
        <tr><th>Fecha</th><th>Usuario</th><th>Correo</th><th>Acción</th><th>Detalles</th></tr>
        <?php if (empty($logs)): ?>
            <tr><td colspan="5" style="text-align:center;">Aún no hay registros de auditoría.</td></tr>
        <?php else: ?>
            <?php foreach ($logs as $l): ?>
            <tr>
                <td><?= htmlspecialchars($l['fecha']) ?></td>
                <td><?= htmlspecialchars($l['usuario']) ?></td>
                <td><?= htmlspecialchars($l['correo']) ?></td>
                <td><?= htmlspecialchars($l['accion']) ?></td>
                <td><?= htmlspecialchars($l['detalles'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>