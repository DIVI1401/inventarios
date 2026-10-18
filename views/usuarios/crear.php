<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <h2>Crear usuario</h2>

    <div class="card shadow-sm p-4 mt-3" style="max-width: 500px; background-color: #ffffff; border-radius: 8px;">
        <form action="index.php?action=usuarios.guardar" method="POST">
            
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input type="email" id="correo" name="correo" class="form-control" placeholder="ejemplo@correo.com" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="idRol" class="form-label">Rol</label>
                <select id="idRol" name="idRol" class="form-select" required>
                    <?php if (isset($roles) && is_array($roles)): ?>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= $rol['idRol'] ?>"><?= htmlspecialchars($rol['nombre']) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="1">Administrador</option>
                        <option value="2">Bodeguero</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Guardar</button>
                <a href="index.php?action=usuarios" class="btn btn-secondary ms-2">Cancelar</a>
            </div>

        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>