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
        if (!$compra) {
            header("Location: index.php?action=compras");
            exit;
        }
        $detalles = $this->compraModel->detallesDeCompra($id);
        require __DIR__ . '/../views/compras/detalle.php';
    }

    public function mostrarFormularioCrear(): void {
        $soloActivos = fn(array $filas) => array_values(array_filter($filas, fn($f) => (int)$f['estado'] === 1));
        $proveedores = $soloActivos((new Proveedor())->listarTodos());
        $almacenes = $soloActivos((new Almacen())->listarTodos());
        $insumos = $soloActivos((new Insumo())->listarTodos());
        $preciosPorProveedor = $this->compraModel->ultimosPreciosPorProveedor();
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

        $vencimientos = $_POST['fechaVencimiento'] ?? [];
        $hoy = date('Y-m-d');

        $detalles = [];
        foreach ($idsInsumo as $i => $idInsumo) {
            if (empty($idInsumo) || empty($cantidades[$i])) continue;

            $factor = !empty($factores[$i]) ? (float)$factores[$i] : 1;
            $cantidadReal = (float)$cantidades[$i] * $factor;
            $precio = (float)($precios[$i] ?? 0);
            $vence = trim($vencimientos[$i] ?? '');

            if ($cantidadReal <= 0 || $precio <= 0) {
                $_SESSION['error'] = "La cantidad y el precio de cada insumo deben ser mayores a cero.";
                header("Location: index.php?action=compras.crear");
                exit;
            }
            if ($vence !== '' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $vence) || $vence < $hoy)) {
                $_SESSION['error'] = "La fecha de vencimiento de un lote no puede ser anterior a hoy.";
                header("Location: index.php?action=compras.crear");
                exit;
            }

            $detalles[] = [
                'idInsumo' => (int)$idInsumo,
                'cantidad' => $cantidadReal,
                'precioUnitario' => $precio,
                'fechaVencimiento' => $vence !== '' ? $vence : null,
            ];
        }

        if (empty($detalles)) {
            $_SESSION['error'] = "Agrega al menos un insumo con cantidad.";
            header("Location: index.php?action=compras.crear");
            exit;
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