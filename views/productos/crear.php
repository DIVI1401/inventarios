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
                <label class="form-label">Precio de Venta ($) *</label>
                <input type="number" step="0.01" name="precio" class="form-control" required placeholder="0.00">
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3" placeholder="Detalles o ingredientes generales"></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?action=productos" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Producto</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>