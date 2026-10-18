<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AetherStock - Iniciar sesión</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <h2>AetherStock</h2>

            <?php if (!empty($_SESSION['error'])): ?>
                <p class="error-msg"><?= htmlspecialchars($_SESSION['error']) ?></p>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <form method="POST" action="index.php?action=doLogin">
                <label>Correo</label>
                <input type="email" name="correo" required>

                <label>Contraseña</label>
                <input type="password" name="password" required>

                <button type="submit">Ingresar</button>
            </form>
        </div>
    </div>
</body>
</html>   