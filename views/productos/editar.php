<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="mb-4">
    <h2>Editar Producto</h2>
</div>

<div class="card shadow-sm col-md-8 mx-auto">
    <div class="card-body">
        <form action="index.php?action=productos.actualizar" method="POST">
            <input type="hidden" name="idProducto" value="<?= $producto['idProducto'] ?>">

            <div class="mb-3">
                <label class="form-label">Nombre del Producto *</label>
                <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($producto['nombre']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Categoría</label>
                <select name="idCategoria" class="form-select">
                    <option value="">-- Seleccionar Categoría --</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['idCategoria'] ?>" <?= $cat['idCategoria'] == $producto['idCategoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Margen de ganancia (%) *</label>
                    <input type="number" step="0.5" min="0" max="<?= Producto::MARGEN_MAXIMO ?>" name="margenGanancia" id="inputMargen" class="form-control" required value="<?= (float)$producto['margenGanancia'] ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Costo de la receta</label>
                    <input type="text" class="form-control" readonly value="$<?= number_format($costoReceta, 2) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Precio de venta (automático)</label>
                    <input type="text" id="precioCalculado" class="form-control fw-bold" readonly
                           value="<?= $costoReceta > 0 ? '$' . number_format((float)$producto['precio'], 2) : 'Sin receta' ?>">
                </div>
                <div class="col-12 mb-3"><div class="form-text">Porcentaje del precio de venta que queda como ganancia. En restaurantes se usa la regla del <em>food cost</em>: los insumos deben costar cerca del 30 % del precio, o sea un margen del 70 %. El precio de venta se calcula solo al guardar la receta.</div></div>
            </div>
            <script>
            (function () {
                const costo = <?= json_encode((float)$costoReceta) ?>, redondeo = <?= Producto::REDONDEO ?>, max = <?= Producto::MARGEN_MAXIMO ?>;
                const margen = document.getElementById('inputMargen'), salida = document.getElementById('precioCalculado');
                margen.addEventListener('input', function () {
                    if (costo <= 0) return;
                    const m = Math.min(max, Math.max(0, parseFloat(margen.value) || 0));
                    const precio = Math.ceil(costo / (1 - m / 100) / redondeo) * redondeo;
                    salida.value = '$' + precio.toLocaleString('es-CO', {minimumFractionDigits: 2});
                });
            })();
            </script>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="1" <?= $producto['estado'] == 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= $producto['estado'] == 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?action=productos" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Producto</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>