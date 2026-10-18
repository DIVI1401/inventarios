<?php
require_once __DIR__ . '/../layouts/header.php';

// Filas que se muestran al cargar (las que el usuario había escrito si hubo error)
$filas = [];
foreach ((array)($old['idProducto'] ?? []) as $i => $idProd) {
    $filas[] = ['idProducto' => (string)$idProd, 'cantidad' => (string)($old['cantidad'][$i] ?? 1)];
}
if (empty($filas)) {
    $filas[] = ['idProducto' => '', 'cantidad' => '1'];
}

$productosJs = array_map(fn($p) => [
    'id' => (string)$p['idProducto'],
    'nombre' => $p['nombre'],
    'precio' => (float)$p['precio'],
], $productos);

$jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
$metodoSel = $old['metodoPago'] ?? 'Efectivo';
?>

<div class="container-fluid px-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-cart-plus me-2"></i>Registrar venta</h2>
        <a href="index.php?action=ventas" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver al historial
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=ventas.guardar" id="formVenta">
        <div class="card mb-4">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Almacén / sucursal</label>
                    <select name="idAlmacen" class="form-select" required>
                        <option value="">-- Selecciona --</option>
                        <?php foreach ($almacenes as $a): ?>
                            <option value="<?= (int)$a['idAlmacen'] ?>" <?= (($old['idAlmacen'] ?? '') == $a['idAlmacen']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Los insumos de las recetas se descontarán de este almacén.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Método de pago</label>
                    <select name="metodoPago" id="metodoPago" class="form-select" required>
                        <?php foreach ($metodosPago as $m): ?>
                            <option value="<?= htmlspecialchars($m) ?>" <?= $metodoSel === $m ? 'selected' : '' ?>><?= htmlspecialchars($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Productos</h5>
                <div class="table-responsive">
                    <table class="table align-middle" id="tablaProductos">
                        <thead>
                            <tr>
                                <th style="min-width:260px;">Producto</th>
                                <th style="width:140px;">Cantidad</th>
                                <th class="text-end" style="width:160px;">Precio unitario</th>
                                <th class="text-end" style="width:160px;">Subtotal</th>
                                <th style="width:60px;"></th>
                            </tr>
                        </thead>
                        <tbody id="filas"></tbody>
                    </table>
                </div>

                <button type="button" id="btnAgregar" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle me-1"></i> Agregar producto
                </button>

                <hr>
                <div class="d-flex justify-content-end align-items-center">
                    <span class="fs-5 me-3">Total:</span>
                    <span class="fs-3 fw-bold" id="totalVenta">$0.00</span>
                </div>

                <div id="boxEfectivo" class="row justify-content-end mt-3 g-3">
                    <div class="col-md-3">
                        <label class="form-label">Monto recibido</label>
                        <input type="number" step="0.01" min="0" name="montoRecibido" id="montoRecibido"
                               class="form-control" value="<?= htmlspecialchars($old['montoRecibido'] ?? '') ?>"
                               placeholder="Vacío = pago exacto">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cambio a devolver</label>
                        <div class="form-control bg-light fw-bold" id="cambio">$0.00</div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-lg">
            <i class="bi bi-check-circle me-1"></i> Guardar venta
        </button>
    </form>
</div>

<script>
    // Los precios se muestran solo como referencia: el servidor usa siempre el precio real guardado en la BD.
    const productos = <?= json_encode($productosJs, $jsonFlags) ?>;
    const filasIniciales = <?= json_encode($filas, $jsonFlags) ?>;

    const tbody = document.getElementById('filas');
    const totalEl = document.getElementById('totalVenta');
    const metodoEl = document.getElementById('metodoPago');
    const boxEfectivo = document.getElementById('boxEfectivo');
    const recibidoEl = document.getElementById('montoRecibido');
    const cambioEl = document.getElementById('cambio');

    const dinero = n => '$' + Number(n).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    function crearFila(idProducto, cantidad) {
        const tr = document.createElement('tr');

        const tdProd = document.createElement('td');
        const select = document.createElement('select');
        select.name = 'idProducto[]';
        select.className = 'form-select';
        const vacio = document.createElement('option');
        vacio.value = '';
        vacio.textContent = '-- Selecciona un producto --';
        select.appendChild(vacio);
        productos.forEach(p => {
            const o = document.createElement('option');
            o.value = p.id;
            o.textContent = p.nombre;
            if (p.id === String(idProducto)) o.selected = true;
            select.appendChild(o);
        });
        tdProd.appendChild(select);

        const tdCant = document.createElement('td');
        const inputCant = document.createElement('input');
        inputCant.type = 'number';
        inputCant.name = 'cantidad[]';
        inputCant.min = '1';
        inputCant.step = '1';
        inputCant.className = 'form-control';
        inputCant.value = cantidad || 1;
        tdCant.appendChild(inputCant);

        const tdPrecio = document.createElement('td');
        tdPrecio.className = 'text-end precio';
        const tdSub = document.createElement('td');
        tdSub.className = 'text-end fw-semibold subtotal';

        const tdDel = document.createElement('td');
        const btnDel = document.createElement('button');
        btnDel.type = 'button';
        btnDel.className = 'btn btn-outline-danger btn-sm';
        btnDel.innerHTML = '<i class="bi bi-trash"></i>';
        btnDel.title = 'Quitar';
        btnDel.addEventListener('click', () => {
            if (tbody.rows.length > 1) { tr.remove(); } else { select.value = ''; inputCant.value = 1; }
            recalcular();
        });
        tdDel.appendChild(btnDel);

        tr.append(tdProd, tdCant, tdPrecio, tdSub, tdDel);
        select.addEventListener('change', recalcular);
        inputCant.addEventListener('input', recalcular);
        tbody.appendChild(tr);
    }

    function recalcular() {
        let total = 0;
        Array.from(tbody.rows).forEach(tr => {
            const id = tr.querySelector('select').value;
            const cant = parseFloat(tr.querySelector('input').value) || 0;
            const prod = productos.find(p => p.id === id);
            const precio = prod ? prod.precio : 0;
            const sub = precio * cant;
            tr.querySelector('.precio').textContent = prod ? dinero(precio) : '—';
            tr.querySelector('.subtotal').textContent = prod ? dinero(sub) : '—';
            total += sub;
        });
        totalEl.textContent = dinero(total);

        const esEfectivo = metodoEl.value === 'Efectivo';
        boxEfectivo.style.display = esEfectivo ? '' : 'none';
        const recibido = recibidoEl.value === '' ? total : parseFloat(recibidoEl.value) || 0;
        cambioEl.textContent = dinero(Math.max(0, recibido - total));
        cambioEl.classList.toggle('text-danger', esEfectivo && recibido < total);
    }

    document.getElementById('btnAgregar').addEventListener('click', () => { crearFila('', 1); recalcular(); });
    metodoEl.addEventListener('change', recalcular);
    recibidoEl.addEventListener('input', recalcular);

    filasIniciales.forEach(f => crearFila(f.idProducto, f.cantidad));
    recalcular();
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
