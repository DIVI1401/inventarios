<?php
require_once __DIR__ . '/../models/Usuario.php';

class PerfilController {
    private Usuario $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    // Mostrar vista de perfil (REQ-41 y REQ-42)
    public function index(): void {
        $idUsuario = $_SESSION['idUsuario'] ?? 0;
        $usuario = $this->usuarioModel->obtenerPorId($idUsuario);
        require __DIR__ . '/../views/perfil/index.php';
    }

    // Procesar actualización de datos personales (REQ-41)
    public function actualizar(): void {
        $idUsuario = $_SESSION['idUsuario'] ?? 0;
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');

        if (!empty($nombre) && !empty($correo)) {
            $exito = $this->usuarioModel->actualizarPerfil($idUsuario, $nombre, $correo);
            if ($exito) {
                $_SESSION['nombre'] = $nombre;
                header('Location: index.php?action=perfil&msj=perfil_ok');
                exit;
            }
        }
        header('Location: index.php?action=perfil&err=perfil_error');
        exit;
    }

    // Procesar cambio de contraseña (REQ-42)
    public function cambiarPassword(): void {
        $idUsuario = $_SESSION['idUsuario'] ?? 0;
        $nuevaPassword = $_POST['nuevaPassword'] ?? '';
        $confirmarPassword = $_POST['confirmarPassword'] ?? '';

        if (!empty($nuevaPassword) && $nuevaPassword === $confirmarPassword) {
            $exito = $this->usuarioModel->cambiarPassword($idUsuario, $nuevaPassword);
            if ($exito) {
                header('Location: index.php?action=perfil&msj=pass_ok');
                exit;
            }
        }
        header('Location: index.php?action=perfil&err=pass_error');
        exit;
    }
}