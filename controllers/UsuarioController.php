<?php

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Log.php';
class UsuarioController {
    private Usuario $usuarioModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->usuarioModel = new Usuario();
    }

    public function listar(): void {
        $usuarios = $this->usuarioModel->listarTodos();
        require __DIR__ . '/../views/usuarios/index.php';
    }

    public function mostrarFormularioCrear(): void {
        $roles = $this->usuarioModel->listarRoles();
        require __DIR__ . '/../views/usuarios/crear.php';
    }

    public function crear(): void {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';
        $idRol = (int)($_POST['idRol'] ?? 0);
        Log::registrar($_SESSION['idUsuario'], 'Creó usuario', "Correo: {$correo}");

        if (empty($nombre) || empty($correo) || empty($password) || !$idRol) {
            $_SESSION['error'] = "Todos los campos son obligatorios.";
            header("Location: index.php?action=usuarios.crear");
            exit;
        }

        // Validación para no duplicar correos
        if ($this->usuarioModel->existeCorreo($correo)) {
            $_SESSION['error'] = "El correo ya se encuentra registrado.";
            header("Location: index.php?action=usuarios.crear");
            exit;
        }

        if ($this->usuarioModel->crear($nombre, $correo, $password, $idRol)) {
            $_SESSION['mensaje'] = "Usuario creado exitosamente.";
        } else {
            $_SESSION['error'] = "No se pudo crear el usuario.";
        }

        header("Location: index.php?action=usuarios");
        exit;
    }

    public function mostrarFormularioEditar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $usuario = $this->usuarioModel->buscarPorId($id);
        $roles = $this->usuarioModel->listarRoles();
        require __DIR__ . '/../views/usuarios/editar.php';
    }

    public function editar(): void {
        $id = (int)($_POST['idUsuario'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $idRol = (int)($_POST['idRol'] ?? 0);
        Log::registrar($_SESSION['idUsuario'], 'Editó usuario', "ID: {$id}");

        if (empty($nombre) || empty($correo) || !$idRol || !$id) {
            $_SESSION['error'] = "Todos los campos son obligatorios.";
            header("Location: index.php?action=usuarios.editar&id={$id}");
            exit;
        }

        // Validar duplicado de correo excluyendo al usuario actual
        if ($this->usuarioModel->existeCorreo($correo, $id)) {
            $_SESSION['error'] = "El correo ya está en uso por otro usuario.";
            header("Location: index.php?action=usuarios.editar&id={$id}");
            exit;
        }

        if ($this->usuarioModel->editar($id, $nombre, $correo, $idRol)) {
            $_SESSION['mensaje'] = "Usuario actualizado correctamente.";
        } else {
            $_SESSION['error'] = "Error al actualizar el usuario.";
        }

        header("Location: index.php?action=usuarios");
        exit;
    }

    public function desactivar(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->usuarioModel->cambiarEstado($id, false)) {
            $_SESSION['mensaje'] = "Usuario desactivado correctamente.";
        }
        Log::registrar($_SESSION['idUsuario'], 'Desactivó usuario', "ID: {$id}");
        header("Location: index.php?action=usuarios");
        exit;
    }

    public function activar(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->usuarioModel->cambiarEstado($id, true)) {
            $_SESSION['mensaje'] = "Usuario activado correctamente.";
        }
        Log::registrar($_SESSION['idUsuario'], 'Activó usuario', "ID: {$id}");
        header("Location: index.php?action=usuarios");
        exit;
    }

    // ===== MÉTODOS DE PERFIL Y AUTOSERVICIO (REQ-41, REQ-42) =====

    public function perfil(): void {
        $idUsuario = $_SESSION['idUsuario'] ?? 0;
        
        // Intenta obtener por buscarPorId o por obtenerPorId según tu modelo
        $usuario = method_exists($this->usuarioModel, 'obtenerPorId') 
            ? $this->usuarioModel->obtenerPorId($idUsuario) 
            : $this->usuarioModel->buscarPorId($idUsuario);
            
        // Ruta corregida según tu árbol de archivos (views/perfil/index.php)
        require __DIR__ . '/../views/perfil/index.php';
    }

    // REQ-41: Guardar datos personales propios
    public function guardarPerfil(): void {
        $idUsuario = $_SESSION['idUsuario'] ?? 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');

        if (empty($nombre) || empty($correo)) {
            $_SESSION['error'] = "El nombre y el correo son obligatorios.";
            header('Location: index.php?action=perfil');
            exit;
        }

        if ($this->usuarioModel->existeCorreo($correo, $idUsuario)) {
            $_SESSION['error'] = "Ese correo ya pertenece a otra cuenta.";
            header('Location: index.php?action=perfil');
            exit;
        }

        $exito = false;
        if (method_exists($this->usuarioModel, 'actualizarPerfil')) {
            $exito = $this->usuarioModel->actualizarPerfil($idUsuario, $nombre, $correo);
        } else {
            // Respeta el método editar si actualizarPerfil no está definido
            $usuarioActual = $this->usuarioModel->buscarPorId($idUsuario);
            $idRol = $usuarioActual['idRol'] ?? $_SESSION['idRol'] ?? 1;
            $exito = $this->usuarioModel->editar($idUsuario, $nombre, $correo, $idRol);
        }

        if ($exito) {
            $_SESSION['nombre'] = $nombre;
            $_SESSION['correo'] = $correo;
            $_SESSION['mensaje'] = "Perfil actualizado con éxito.";
            header('Location: index.php?action=perfil&msj=perfil_ok');
            exit;
        }

        $_SESSION['error'] = "No se pudo actualizar el perfil.";
        header('Location: index.php?action=perfil&err=perfil_error');
        exit;
    }

    // REQ-42: Cambiar la contraseña propia
    public function cambiarPassword(): void {
        $idUsuario = $_SESSION['idUsuario'] ?? 0;
        $nuevaPassword = $_POST['nuevaPassword'] ?? '';
        $confirmarPassword = $_POST['confirmarPassword'] ?? '';

        if (empty($nuevaPassword)) {
            $_SESSION['error'] = "La contraseña no puede estar vacía.";
            header('Location: index.php?action=perfil');
            exit;
        }

        if ($nuevaPassword !== $confirmarPassword) {
            $_SESSION['error'] = "Las contraseñas no coinciden.";
            header('Location: index.php?action=perfil');
            exit;
        }

        // Genera el hash de la contraseña una sola vez en el controlador
        $hashPassword = password_hash($nuevaPassword, PASSWORD_DEFAULT);

        $exito = false;
        if (method_exists($this->usuarioModel, 'cambiarPassword')) {
            $exito = $this->usuarioModel->cambiarPassword($idUsuario, $hashPassword);
        } elseif (method_exists($this->usuarioModel, 'actualizarContraseña')) {
            $exito = $this->usuarioModel->actualizarContraseña($idUsuario, $hashPassword);
        }

        if ($exito) {
            $_SESSION['mensaje'] = "Contraseña cambiada exitosamente.";
            header('Location: index.php?action=perfil&msj=pass_ok');
            exit;
        }

        $_SESSION['error'] = "Error al intentar cambiar la contraseña.";
        header('Location: index.php?action=perfil&err=pass_error');
        exit;
    }
}