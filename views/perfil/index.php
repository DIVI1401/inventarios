<?php require __DIR__ . '/../layouts/header.php'; ?>

<h2>Mi Perfil</h2>

<?php if (isset($_SESSION['mensaje'])): ?>
    <p style="color: green; font-weight: bold; background: #d4edda; padding: 10px; border-radius: 4px;">
        <?= htmlspecialchars($_SESSION['mensaje']) ?>
    </p>
    <?php unset($_SESSION['mensaje']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <p style="color: red; font-weight: bold; background: #f8d7da; padding: 10px; border-radius: 4px;">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </p>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_GET['msj']) && $_GET['msj'] === 'perfil_ok'): ?>
    <p style="color: green; font-weight: bold;">Datos personales actualizados correctamente.</p>
<?php endif; ?>

<?php if (isset($_GET['msj']) && $_GET['msj'] === 'pass_ok'): ?>
    <p style="color: green; font-weight: bold;">Contraseña cambiada con éxito.</p>
<?php endif; ?>

<!-- REQ-41: Formulario de datos personales -->
<div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px; max-width: 500px;">
    <h3>Editar Datos Personales (REQ-41)</h3>
    <form action="index.php?action=guardarPerfil" method="POST">
        <div style="margin-bottom: 15px;">
            <label>Nombre:</label><br>
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre'] ?? $_SESSION['nombre'] ?? '') ?>" required style="width: 100%; padding: 8px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label>Correo Electrónico:</label><br>
            <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo'] ?? $_SESSION['correo'] ?? '') ?>" required style="width: 100%; padding: 8px;">
        </div>
        <button type="submit" style="padding: 10px 15px; background: #0d6efd; color: white; border: none; cursor: pointer; border-radius: 4px;">Guardar Cambios</button>
    </form>
</div>

<!-- REQ-42: Formulario de cambio de contraseña -->
<div style="background: #f8f9fa; padding: 20px; border-radius: 5px; max-width: 500px;">
    <h3>Cambiar Contraseña (REQ-42)</h3>
    <form action="index.php?action=cambiarPassword" method="POST">
        <div style="margin-bottom: 15px;">
            <label>Nueva Contraseña:</label><br>
            <input type="password" name="nuevaPassword" required style="width: 100%; padding: 8px;">
        </div>
        <div style="margin-bottom: 15px;">
            <label>Confirmar Nueva Contraseña:</label><br>
            <input type="password" name="confirmarPassword" required style="width: 100%; padding: 8px;">
        </div>
        <button type="submit" style="padding: 10px 15px; background: #198754; color: white; border: none; cursor: pointer; border-radius: 4px;">Actualizar Contraseña</button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>