<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Registrar merma / pérdida</h2>

<?php if (!empty($_SESSION['error'])): ?>
    <p class="error-msg"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="panel">
    <form method="POST" action="index.php?action=mermas.guardar" class="formulario">
        <label>Almacén</label>
        <select name="idAlmacen" required>
            <?php foreach ($almacenes as $a): ?>
                <option value="<?= $a['idAlmacen'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Insumo</label>
        <select name="idInsumo" required>
            <?php foreach ($insumos as $i): ?>
                <option value="<?= $i['idInsumo'] ?>"><?= htmlspecialchars($i['nombre']) ?> (<?= $i['unidadMedida'] ?>)</option>
            <?php endforeach; ?>
        </select>

        <label>Cantidad</label>
        <input type="number" step="0.01" name="cantidad" required>

        <label>Motivo</label>
        <select name="motivo" required>
            <option value="Vencimiento">Vencimiento</option>
            <option value="Daño">Daño / deterioro</option>
            <option value="Error de manejo">Error de manejo</option>
            <option value="Robo">Robo</option>
            <option value="Otro">Otro</option>
        </select>

        <button type="submit">Registrar</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>