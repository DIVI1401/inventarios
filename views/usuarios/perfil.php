<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <h2>Mi Perfil</h2>
    
    <!-- Mapeo de mensajes de éxito y error -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- REQ-41: Editar Perfil -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">Información Personal</div>
                <div class="card-body">
                    <form action="index.php?action=guardarPerfil" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- REQ-42: Restablecer Contraseña -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">Cambiar Contraseña</div>
                <div class="card-body">
                    <form action="index.php?action=cambiarPassword" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="nueva_password" class="form-control" required minlength="4">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" name="confirmar_password" class="form-control" required minlength="4">
                        </div>
                        <button type="submit" class="btn btn-warning">Actualizar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>