<?php

require_once __DIR__ . '/../models/Proveedor.php';

class ProveedorController {
    private Proveedor $proveedorModel;

    public function __construct() {
        $this->proveedorModel = new Proveedor();
    }

    public function listar(): void {
        $proveedores = $this->proveedorModel->listarTodos();
        require __DIR__ . '/../views/proveedores/index.php';
    }

    public function mostrarFormularioCrear(): void {
        require __DIR__ . '/../views/proveedores/crear.php';
    }

    public function crear(): void {
    $nombre = trim($_POST['nombre'] ?? '');
    $contacto = trim($_POST['contacto'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    $errores = $this->proveedorModel->validar($nombre, $contacto, $telefono, $correo);

    if (!empty($errores)) {
        $_SESSION['error'] = implode(' ', $errores);
        header("Location: index.php?action=proveedores.crear");
        exit;
    }

    $this->proveedorModel->crear($nombre, $contacto, $telefono, $correo, $direccion);
    header("Location: index.php?action=proveedores");
    exit;
}

    public function mostrarFormularioEditar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $proveedor = $this->proveedorModel->buscarPorId($id);
        require __DIR__ . '/../views/proveedores/editar.php';
    }

    public function editar(): void {
    $id = (int)($_POST['idProveedor'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $contacto = trim($_POST['contacto'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    $errores = $this->proveedorModel->validar($nombre, $contacto, $telefono, $correo);

    if (!empty($errores)) {
        $_SESSION['error'] = implode(' ', $errores);
        header("Location: index.php?action=proveedores.editar&id={$id}");
        exit;
    }

    $this->proveedorModel->editar($id, $nombre, $contacto, $telefono, $correo, $direccion);
    header("Location: index.php?action=proveedores");
    exit;
}

    public function desactivar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->proveedorModel->cambiarEstado($id, false);
        header("Location: index.php?action=proveedores");
        exit;
    }

    public function activar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->proveedorModel->cambiarEstado($id, true);
        header("Location: index.php?action=proveedores");
        exit;
    }
}
