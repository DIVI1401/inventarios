<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2>Transferencia de Insumos entre Almacenes (REQ-22)</h2>

<?php if (isset($_GET['err'])): ?>
    <p style="color: red; font-weight: bold;">Error al realizar la transferencia. Verifica el stock en el almacén origen.</p>
<?php endif; ?>

<form action="index.php?action=inventario.guardarTransferencia" method="POST" style="max-width: 500px; margin-top: 20px;">
    <div style="margin-bottom: 15px;">
        <label>Insumo:</label><br>
        <select name="idInsumo" required style="width: 100%; padding: 8px;">
            <option value="">-- Seleccionar Insumo --</option>
            <?php foreach ($insumos as $insumo): ?>
                <option value="<?= $insumo['idInsumo'] ?>"><?= htmlspecialchars($insumo['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label>Almacén Origen:</label><br>
        <select name="idOrigen" required style="width: 100%; padding: 8px;">
            <option value="">-- Seleccionar Origen --</option>
            <?php foreach ($almacenes as $alm): ?>
                <option value="<?= $alm['idAlmacen'] ?>"><?= htmlspecialchars($alm['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label>Almacén Destino:</label><br>
        <select name="idDestino" required style="width: 100%; padding: 8px;">
            <option value="">-- Seleccionar Destino --</option>
            <?php foreach ($almacenes as $alm): ?>
                <option value="<?= $alm['idAlmacen'] ?>"><?= htmlspecialchars($alm['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label>Cantidad a Transferir:</label><br>
        <input type="number" step="0.01" name="cantidad" min="0.01" required style="width: 100%; padding: 8px;">
    </div>

    <button type="submit" style="padding: 10px 20px; background: #0d6efd; color: white; border: none; cursor: pointer;">Procesar Transferencia</button>
</form>

<?php require __DIR__ . '/../layouts/footer.php'; ?>