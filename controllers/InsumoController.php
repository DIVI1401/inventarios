<?php

require_once __DIR__ . '/../models/Insumo.php';
require_once __DIR__ . '/../models/Compra.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Registro.php';
class InsumoController {
    private Insumo $insumoModel;

    public function __construct() {
        $this->insumoModel = new Insumo();
    }

    public function listar(): void {
        $insumos = $this->insumoModel->listarTodos();
        require __DIR__ . '/../views/insumos/index.php';
    }

    public function mostrarFormularioCrear(): void {
        $categorias = Registro::filtrarActivos((new Categoria())->listarTodas(), 'idCategoria');
        require __DIR__ . '/../views/insumos/crear.php';
    }

    public function crear(): void {
        $nombre = trim($_POST['nombre'] ?? '');
        $unidadMedida = $_POST['unidadMedida'] ?? '';
        $precioUnitario = (float)($_POST['precioUnitario'] ?? 0);
        $stockMinimo = (float)($_POST['stockMinimo'] ?? 0);
        $idCategoria = (int)($_POST['idCategoria'] ?? 0) ?: null;

        $errores = $this->insumoModel->validar($nombre, $precioUnitario, $stockMinimo);
        if ($idCategoria && !Registro::estaActivo('categorias', $idCategoria)) {
            $errores[] = "La categoría seleccionada está desactivada.";
        }

        if (!empty($errores)) {
            $_SESSION['error'] = implode(' ', $errores);
            header("Location: index.php?action=insumos.crear");
            exit;
        }

        $this->insumoModel->crear($nombre, $unidadMedida, $precioUnitario, $stockMinimo, $idCategoria);
        header("Location: index.php?action=insumos");
        exit;
    }

    public function mostrarFormularioEditar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $insumo = $this->insumoModel->buscarPorId($id);
        if (!$insumo) {
            header("Location: index.php?action=insumos");
            exit;
        }
        $categorias = Registro::filtrarActivos((new Categoria())->listarTodas(), 'idCategoria', [(int)$insumo['idCategoria']]);
        require __DIR__ . '/../views/insumos/editar.php';
    }

    public function editar(): void {
        $id = (int)($_POST['idInsumo'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $unidadMedida = $_POST['unidadMedida'] ?? '';
        $precioUnitario = (float)($_POST['precioUnitario'] ?? 0);
        $stockMinimo = (float)($_POST['stockMinimo'] ?? 0);
        $idCategoria = (int)($_POST['idCategoria'] ?? 0) ?: null;

        $errores = $this->insumoModel->validar($nombre, $precioUnitario, $stockMinimo);
        $actual = $this->insumoModel->buscarPorId($id);
        if ($idCategoria && $idCategoria !== (int)($actual['idCategoria'] ?? 0) && !Registro::estaActivo('categorias', $idCategoria)) {
            $errores[] = "La categoría seleccionada está desactivada.";
        }

        if (!empty($errores)) {
            $_SESSION['error'] = implode(' ', $errores);
            header("Location: index.php?action=insumos.editar&id={$id}");
            exit;
        }

        $this->insumoModel->editar($id, $nombre, $unidadMedida, $precioUnitario, $stockMinimo, $idCategoria);
        // El costo de las recetas que usan este insumo cambió: actualiza sus precios de venta
        (new Producto())->recalcularPrecios(idInsumo: $id);
        header("Location: index.php?action=insumos");
        exit;
    }

    public function desactivar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->insumoModel->cambiarEstado($id, false);
        header("Location: index.php?action=insumos");
        exit;
    }

    public function activar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $this->insumoModel->cambiarEstado($id, true);
        header("Location: index.php?action=insumos");
        exit;
    }

    // Requisito 39
    public function historialPrecios(): void {
        $idInsumo = (int)($_GET['idInsumo'] ?? 0);
        $insumos = $this->insumoModel->listarTodos();
        $historial = $idInsumo ? (new Compra())->historialPrecioPorInsumo($idInsumo) : [];
        require __DIR__ . '/../views/reportes/historial_precios.php';
    }

    // Requisito 40
    public function comparativaPrecios(): void {
        $idInsumo = (int)($_GET['idInsumo'] ?? 0);
        $insumos = $this->insumoModel->listarTodos();
        $comparativa = $idInsumo ? (new Compra())->comparativaPrecioPorInsumo($idInsumo) : [];
        require __DIR__ . '/../views/reportes/comparativa_precios.php';
    }
}