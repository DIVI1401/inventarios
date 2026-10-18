<?php

require_once __DIR__ . '/../models/Compra.php';
require_once __DIR__ . '/../models/Proveedor.php';
require_once __DIR__ . '/../models/Almacen.php';
require_once __DIR__ . '/../models/Insumo.php';

class CompraController {
    private Compra $compraModel;

    public function __construct() {
        $this->compraModel = new Compra();
    }

    public function listar(): void {
        $compras = $this->compraModel->listarTodas();
        require __DIR__ . '/../views/compras/index.php';
    }

    public function verDetalle(): void {
        $id = (int)($_GET['id'] ?? 0);
        $compra = $this->compraModel->buscarPorId($id);
        $detalles = $this->compraModel->detallesDeCompra($id);
        require __DIR__ . '/../views/compras/detalle.php';
    }

    public function mostrarFormularioCrear(): void {
    $proveedores = (new Proveedor())->listarTodos();
    $almacenes = (new Almacen())->listarTodos();
    $insumos = (new Insumo())->listarTodos();
    $preciosPorProveedor = (new Compra())->ultimosPreciosPorProveedor();
    require __DIR__ . '/../views/compras/crear.php';
}

    public function crear(): void {
        $idProveedor = (int)($_POST['idProveedor'] ?? 0);
        $idAlmacen = (int)($_POST['idAlmacen'] ?? 0);
        $idUsuario = $_SESSION['idUsuario'];

        $idsInsumo = $_POST['idInsumo'] ?? [];
        $cantidades = $_POST['cantidad'] ?? [];
        $precios = $_POST['precioUnitario'] ?? [];
        $factores = $_POST['factorConversion'] ?? [];

        if (!$idProveedor || !$idAlmacen || empty($idsInsumo)) {
            $_SESSION['error'] = "Debes seleccionar proveedor, almacén y al menos un insumo.";
            header("Location: index.php?action=compras.crear");
            exit;
        }

        $detalles = [];
            foreach ($idsInsumo as $i => $idInsumo) {
            if (empty($idInsumo) || empty($cantidades[$i])) continue;

        $factor = !empty($factores[$i]) ? (float)$factores[$i] : 1;
        $cantidadReal = (float)$cantidades[$i] * $factor;

        $detalles[] = [
            'idInsumo' => (int)$idInsumo,
            'cantidad' => $cantidadReal,
            'precioUnitario' => (float)$precios[$i],
            ];
        }

        $ok = $this->compraModel->crear($idProveedor, $idAlmacen, $idUsuario, $detalles);

        if (!$ok) {
            $_SESSION['error'] = "Ocurrió un error al registrar la compra.";
            header("Location: index.php?action=compras.crear");
            exit;
        }

        header("Location: index.php?action=compras");
        exit;
    }
}