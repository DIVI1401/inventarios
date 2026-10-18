<?php

require_once __DIR__ . '/../models/Almacen.php';

class AlmacenController {
    private Almacen $almacenModel;

    public function __construct() {
        $this->almacenModel = new Almacen();
    }

    public function listar(): void {
        $almacenes = $this->almacenModel->listarTodos();
        require __DIR__ . '/../views/almacenes/index.php';
    }

    public function mostrarFormularioCrear(): void {
        require __DIR__ . '/../views/almacenes/crear.php';
    }

    public function crear(): void {
        $nombre = $_POST['nombre'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        if (empty($nombre) || empty($ubicacion)) {
            $_SESSION['error'] = "Nombre y ubicación son obligatorios.";
            header("Location: index.php?action=almacenes.crear");
            exit;
        }

        $this->almacenModel->crear($nombre, $ubicacion, $descripcion);
        header("Location: index.php?action=almacenes");
        exit;
    }

    public function mostrarFormularioEditar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $almacen = $this->almacenModel->buscarPorId($id);
        require __DIR__ . '/../views/almacenes/editar.php';
    }

    public function editar(): void {
        $id = (int)($_POST['idAlmacen'] ?? 0);
        $nombre = $_POST['nombre'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        $this->almacenModel->editar($id, $nombre, $ubicacion, $descripcion);
        header("Location: index.php?action=almacenes");
        exit;
    }

    public function desactivar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->almacenModel->cambiarEstado($id, false);
        header("Location: index.php?action=almacenes");
        exit;
    }

    public function activar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->almacenModel->cambiarEstado($id, true);
        header("Location: index.php?action=almacenes");
        exit;
    }
}