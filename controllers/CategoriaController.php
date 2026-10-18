<?php

require_once __DIR__ . '/../models/Categoria.php';

class CategoriaController {
    private Categoria $categoriaModel;

    public function __construct() {
        $this->categoriaModel = new Categoria();
    }

    public function listar(): void {
        $categorias = $this->categoriaModel->listarTodas();
        require __DIR__ . '/../views/categorias/index.php';
    }

    public function mostrarFormularioCrear(): void {
        require __DIR__ . '/../views/categorias/crear.php';
    }

    public function crear(): void {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (empty($nombre)) {
            $_SESSION['error'] = "El nombre de la categoría es obligatorio.";
            header("Location: index.php?action=categorias.crear");
            exit;
        }

        $this->categoriaModel->crear($nombre, $descripcion);
        header("Location: index.php?action=categorias");
        exit;
    }

    public function mostrarFormularioEditar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $categoria = $this->categoriaModel->buscarPorId($id);
        require __DIR__ . '/../views/categorias/editar.php';
    }

    public function editar(): void {
        $id = (int)($_POST['idCategoria'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        $this->categoriaModel->editar($id, $nombre, $descripcion);
        header("Location: index.php?action=categorias");
        exit;
    }

    public function desactivar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->categoriaModel->cambiarEstado($id, false);
        header("Location: index.php?action=categorias");
        exit;
    }

    public function activar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->categoriaModel->cambiarEstado($id, true);
        header("Location: index.php?action=categorias");
        exit;
    }
}