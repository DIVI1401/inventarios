<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Mermas / Pérdidas</h2>

<a href="index.php?action=mermas.crear" class="btn">+ Registrar merma</a>

<div class="panel">
    <form method="GET" action="index.php" class="formulario">
        <input type="hidden" name="action" value="mermas">
        <label>Filtrar por almacén</label>
        <select name="idAlmacen" onchange="this.form.submit()">
            <option value="">-- Todos los almacenes --</option>
            <?php foreach ($almacenes as $a): ?>
                <option value="<?= $a['idAlmacen'] ?>" <?= $idAlmacenSeleccionado == $a['idAlmacen'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="panel">
    <table>
        <tr><th>Fecha</th><th>Insumo</th><th>Almacén</th><th>Cantidad</th><th>Motivo</th><th>Registrado por</th></tr>
        <?php foreach ($mermas as $m): ?>
        <tr>
            <td><?= htmlspecialchars($m['fechaMerma']) ?></td>
            <td><?= htmlspecialchars($m['nombreInsumo']) ?></td>
            <td><?= htmlspecialchars($m['nombreAlmacen']) ?></td>
            <td><?= $m['cantidad'] ?> <?= htmlspecialchars($m['unidadMedida']) ?></td>
            <td><?= htmlspecialchars($m['motivo']) ?></td>
            <td><?= htmlspecialchars($m['nombreUsuario']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>