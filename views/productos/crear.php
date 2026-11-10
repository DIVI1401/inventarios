<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="mb-4">
    <h2>Registrar Producto</h2>
</div>

<div class="card shadow-sm col-md-8 mx-auto">
    <div class="card-body">
        <form action="index.php?action=productos.guardar" method="POST">
            <div class="mb-3">
                <label class="form-label">Nombre del Producto *</label>
                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Hamburguesa Especial">
            </div>

            <div class="mb-3">
                <label class="form-label">Categoría</label>
                <select name="idCategoria" class="form-select">
                    <option value="">-- Seleccionar Categoría --</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['idCategoria'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Margen de ganancia (%) *</label>
                <input type="number" step="0.5" min="0" max="<?= Producto::MARGEN_MAXIMO ?>" name="margenGanancia" class="form-control" required value="<?= Producto::MARGEN_POR_DEFECTO ?>">
                <div class="form-text">Porcentaje del precio de venta que queda como ganancia. En restaurantes se usa la regla del <em>food cost</em>: los insumos deben costar cerca del 30 % del precio, o sea un margen del 70 %. El precio de venta se calcula solo al guardar la receta.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3" placeholder="Detalles o ingredientes generales"></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?action=productos" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar y armar receta</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>