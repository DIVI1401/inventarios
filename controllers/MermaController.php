<?php

require_once __DIR__ . '/../models/Merma.php';
require_once __DIR__ . '/../models/Almacen.php';
require_once __DIR__ . '/../models/Insumo.php';
require_once __DIR__ . '/../models/Registro.php';

class MermaController {
    private Merma $mermaModel;

    public function __construct() {
        $this->mermaModel = new Merma();
    }

    public function listar(): void {
    $idAlmacenSeleccionado = (int)($_GET['idAlmacen'] ?? 0);
    $mermas = $this->mermaModel->listarPorAlmacen($idAlmacenSeleccionado ?: null);
    $almacenes = (new Almacen())->listarTodos();
    require __DIR__ . '/../views/mermas/index.php';
}
    public function mostrarFormularioCrear(): void {
        $almacenes = Registro::filtrarActivos((new Almacen())->listarTodos(), 'idAlmacen');
        $insumos = Registro::filtrarActivos((new Insumo())->listarTodos(), 'idInsumo');
        require __DIR__ . '/../views/mermas/crear.php';
    }

    public function crear(): void {
        $idInsumo = (int)($_POST['idInsumo'] ?? 0);
        $idAlmacen = (int)($_POST['idAlmacen'] ?? 0);
        $cantidad = (float)($_POST['cantidad'] ?? 0);
        $motivo = $_POST['motivo'] ?? '';
        $idUsuario = $_SESSION['idUsuario'];

        if (!$idInsumo || !$idAlmacen || $cantidad <= 0 || empty($motivo)) {
            $_SESSION['error'] = "Todos los campos son obligatorios y la cantidad debe ser mayor a 0.";
            header("Location: index.php?action=mermas.crear");
            exit;
        }

        if (!Registro::estaActivo('insumos', $idInsumo) || !Registro::estaActivo('almacenes', $idAlmacen)) {
            $_SESSION['error'] = "El insumo o el almacén seleccionado está desactivado.";
            header("Location: index.php?action=mermas.crear");
            exit;
        }

        $resultado = $this->mermaModel->crear($idInsumo, $idAlmacen, $idUsuario, $cantidad, $motivo);

        if (!$resultado['exito']) {
            $_SESSION['error'] = $resultado['error'];
            header("Location: index.php?action=mermas.crear");
            exit;
        }

        header("Location: index.php?action=mermas");
        exit;
    }
}