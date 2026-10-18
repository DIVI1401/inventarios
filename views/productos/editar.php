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

            <div class="mb-3">
                <label class="form-label">Precio de Venta ($) *</label>
                <input type="number" step="0.01" name="precio" class="form-control" required value="<?= $producto['precio'] ?>">
            </div>

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