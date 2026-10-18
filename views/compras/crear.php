<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2 style="margin-bottom:20px;">Registrar compra</h2>

<?php if (!empty($_SESSION['error'])): ?>
    <p class="error-msg"><?= htmlspecialchars($_SESSION['error']) ?></p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="panel">
    <form method="POST" action="index.php?action=compras.guardar" class="formulario" style="max-width: 900px;">
        <label>Proveedor</label>
        <select name="idProveedor" id="selectProveedor" required onchange="mostrarContacto(this)">
            <option value="">-- Selecciona --</option>
            <?php foreach ($proveedores as $p): ?>
                <option value="<?= $p['idProveedor'] ?>" data-contacto="<?= htmlspecialchars($p['contacto']) ?>" data-telefono="<?= htmlspecialchars($p['telefono']) ?>">
                    <?= htmlspecialchars($p['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p id="infoContacto" style="color:#666; font-size:13px; margin-top:6px; margin-bottom:18px; font-style:italic;"></p>

        <label>Almacén / sucursal</label>
        <select name="idAlmacen" required>
            <?php foreach ($almacenes as $a): ?>
                <option value="<?= $a['idAlmacen'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
            <?php endforeach; ?>
        </select>

        <h3 style="margin-top:20px;">Insumos</h3>
        <table id="tablaInsumos">
            <tr>
                <th>Insumo</th>
                <th>Cantidad comprada</th>
                <th>Factor conversión</th>
                <th>Precio unitario (base)</th>
            </tr>
            <tr>
                <td>
                    <select name="idInsumo[]" class="select-insumo" onchange="actualizarPrecio(this)">
                        <?php foreach ($insumos as $i): ?>
                            <option value="<?= $i['idInsumo'] ?>" data-precio="<?= $i['precioUnitario'] ?>">
                                <?= htmlspecialchars($i['nombre']) ?> (<?= $i['unidadMedida'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="number" step="0.01" name="cantidad[]"></td>
                <td><input type="number" step="0.01" name="factorConversion[]" value="1" title="Ej: si compras por caja y trae 24 unidades, pon 24"></td>
                <td><input type="number" step="0.01" name="precioUnitario[]" class="input-precio"></td>
            </tr>
        </table>

        <button type="button" onclick="agregarFila()" class="btn" style="margin-top:10px;">+ Agregar insumo</button>
        <br><br>
        <button type="submit">Guardar compra</button>
    </form>
</div>

<script>
    // Mapa de precios: "idProveedor-idInsumo" => último precio pagado
    const preciosPorCombo = {
        <?php foreach ($preciosPorProveedor as $p): ?>
            "<?= $p['idProveedor'] ?>-<?= $p['idInsumo'] ?>": <?= $p['precioUnitario'] ?>,
        <?php endforeach; ?>
    };

    function actualizarPrecio(selectInsumo) {
        const fila = selectInsumo.closest('tr');
        const idProveedor = document.getElementById('selectProveedor').value;
        const idInsumo = selectInsumo.value;
        const clave = idProveedor + '-' + idInsumo;
        const inputPrecio = fila.querySelector('.input-precio');
        const precioReferencia = selectInsumo.options[selectInsumo.selectedIndex].dataset.precio;

        if (preciosPorCombo.hasOwnProperty(clave)) {
            // Ya le has comprado este insumo a este proveedor antes: usa ese precio
            inputPrecio.value = preciosPorCombo[clave];
        } else {
            // Primera vez con este proveedor: usa el precio de referencia del insumo
            inputPrecio.value = precioReferencia;
        }
    }

    function mostrarContacto(select) {
        const opcion = select.options[select.selectedIndex];
        const contacto = opcion.dataset.contacto;
        const telefono = opcion.dataset.telefono;
        const info = document.getElementById('infoContacto');

        if (contacto || telefono) {
            info.textContent = 'Entrega: ' + (contacto || 'sin nombre registrado') + (telefono ? ' — ' + telefono : '');
        } else {
            info.textContent = '';
        }

        document.querySelectorAll('.select-insumo').forEach(actualizarPrecio);
    }

    function agregarFila() {
        const tabla = document.getElementById('tablaInsumos');
        const filaBase = tabla.rows[1];
        const nuevaFila = filaBase.cloneNode(true);
        nuevaFila.querySelectorAll('input').forEach(input => input.value = '');
        nuevaFila.querySelector('input[name="factorConversion[]"]').value = '1';
        tabla.appendChild(nuevaFila);

        const select = nuevaFila.querySelector('.select-insumo');
        actualizarPrecio(select);
    }

    // Autocompletar el precio de la primera fila al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.select-insumo').forEach(actualizarPrecio);
    });
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>