<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Receta: <?= htmlspecialchars($producto['nombre']) ?></h2>
    <div>
        <?php if (!empty($receta)): ?>
        <a href="index.php?action=productos.eliminarReceta&id=<?= $producto['idProducto'] ?>" 
           class="btn btn-outline-danger"
           onclick="return confirm('¿Seguro que quieres eliminar toda la receta de este producto?');">
            Eliminar Receta
        </a>
        <?php endif; ?>
        <a href="index.php?action=productos" class="btn btn-secondary">Volver a Productos</a>
    </div>
</div>

<form action="index.php?action=productos.guardarReceta" method="POST">
    <input type="hidden" name="idProducto" value="<?= $producto['idProducto'] ?>">

    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Insumos de la Receta (Req. 27)</h5>
                    <button type="button" class="btn" id="btnAgregarInsumo" style="padding: 6px 14px; font-size: 14px;">
                        <i class="bi bi-plus"></i> Agregar Insumo
                    </button>
                </div>
                <div class="card-body">
                    <div id="contenedorInsumos">
                        <?php if (!empty($insumosReceta)): ?>
                            <?php foreach ($insumosReceta as $item): ?>
                                <div class="row mb-3 fila-insumo align-items-center">
                                    <div class="col-md-6">
                                        <select name="insumos[]" class="form-select" required>
                                            <option value="">-- Insumo --</option>
                                            <?php foreach ($todosInsumos as $ins): ?>
                                                <option value="<?= $ins['idInsumo'] ?>" <?= $ins['idInsumo'] == $item['idInsumo'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($ins['nombre']) ?> (<?= $ins['unidadMedida'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" step="0.001" name="cantidades[]" class="form-control" placeholder="Cantidad" value="<?= $item['cantidad'] ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger btnEliminarFila"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="row mb-3 fila-insumo align-items-center">
                                <div class="col-md-6">
                                    <select name="insumos[]" class="form-select" required>
                                        <option value="">-- Insumo --</option>
                                        <?php foreach ($todosInsumos as $ins): ?>
                                            <option value="<?= $ins['idInsumo'] ?>">
                                                <?= htmlspecialchars($ins['nombre']) ?> (<?= $ins['unidadMedida'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="number" step="0.001" name="cantidades[]" class="form-control" placeholder="Cantidad" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger btnEliminarFila"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Instrucciones de Preparación</h5>
                </div>
                <div class="card-body">
                    <textarea name="instrucciones" class="form-control" rows="8" placeholder="Escribe aquí los pasos de la receta..."><?= htmlspecialchars($receta['instrucciones'] ?? '') ?></textarea>
                </div>
            </div>

            <?php if ($costoReceta > 0): ?>
            <div class="card shadow-sm mb-4 border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Costo de preparación</h6>
                    <h3 class="text-success">$<?= number_format($costoReceta, 2) ?></h3>
                    <small class="text-muted">
                        Precio de venta: $<?= number_format($producto['precio'], 2) ?> — 
                        Margen: $<?= number_format($producto['precio'] - $costoReceta, 2) ?>
                    </small>
                </div>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary w-100 btn-lg">Guardar Receta Completa</button>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contenedor = document.getElementById('contenedorInsumos');
    const btnAgregar = document.getElementById('btnAgregarInsumo');

    btnAgregar.addEventListener('click', function() {
        const primeraFila = contenedor.querySelector('.fila-insumo');
        if (primeraFila) {
            const nuevaFila = primeraFila.cloneNode(true);
            nuevaFila.querySelectorAll('input').forEach(i => i.value = '');
            nuevaFila.querySelectorAll('select').forEach(s => s.value = '');
            contenedor.appendChild(nuevaFila);
        }
    });

    contenedor.addEventListener('click', function(e) {
        if (e.target.closest('.btnEliminarFila')) {
            const filas = contenedor.querySelectorAll('.fila-insumo');
            if (filas.length > 1) {
                e.target.closest('.fila-insumo').remove();
            } else {
                alert('La receta debe contener al menos un insumo.');
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>