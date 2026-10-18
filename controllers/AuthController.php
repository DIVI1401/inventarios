<?php

require_once __DIR__ . '/../models/Usuario.php';

class AuthController {

    // Cargar formulario de login
    public function mostrarLogin(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Si ya hay una sesión activa, redirige directamente al dashboard
        if (isset($_SESSION['idUsuario'])) {
            header("Location: index.php?action=dashboard");
            exit;
        }

        require __DIR__ . '/../views/login/index.php';
    }

    // Procesar autenticación de usuario
    public function login(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $correo = trim($_POST['correo'] ?? '');
        // Captura tanto si el formulario envía 'password' como 'contraseña'
        $password = $_POST['password'] ?? $_POST['contraseña'] ?? '';

        if (empty($correo) || empty($password)) {
            $_SESSION['error'] = "Debes llenar todos los campos.";
            header("Location: index.php?action=login");
            exit;
        }

        $usuarioModel = new Usuario();
        // Utiliza el método login del modelo que valida de forma robusta
        $usuario = $usuarioModel->login($correo, $password);

        // Validación de existencia de usuario y credenciales
        if (!$usuario) {
            $_SESSION['error'] = "Correo o contraseña incorrectos.";
            header("Location: index.php?action=login");
            exit;
        }

        require_once __DIR__ . '/../models/Log.php';
        Log::registrar($usuario['idUsuario'], 'Inicio de sesión');

        // Prevenir fijación de sesión regenerando el ID
        session_regenerate_id(true);

        // Login exitoso: Almacenamiento de variables de sesión obligatorias
        $_SESSION['idUsuario'] = $usuario['idUsuario'];
        $_SESSION['nombre']    = $usuario['nombre'];
        $_SESSION['correo']    = $usuario['correo'];
        $_SESSION['idRol']     = $usuario['idRol'];
        // El rol en la BD se llama 'Bodeguista' pero el sistema valida 'Bodeguero'; se unifica aquí
        $rolSesion = $usuario['nombreRol'] ?? 'Administrador';
        $_SESSION['rol']       = ($rolSesion === 'Bodeguista') ? 'Bodeguero' : $rolSesion;

        header("Location: index.php?action=dashboard");
        exit;
    }

    // Cierre seguro de sesión
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        session_destroy();
        header("Location: index.php?action=login");
        exit;
    }
}