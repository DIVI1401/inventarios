<?php

require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/Almacen.php';
require_once __DIR__ . '/../models/Registro.php';

class InventarioController {
    private Inventario $inventarioModel;

    public function __construct() {
        $this->inventarioModel = new Inventario();
    }

    public function listar(): void {
        $almacenes = (new Almacen())->listarTodos();
        $idAlmacenSeleccionado = (int)($_GET['idAlmacen'] ?? 0);

        $inventario = [];
        if ($idAlmacenSeleccionado) {
            $inventario = $this->inventarioModel->porAlmacen($idAlmacenSeleccionado);
        }

        require __DIR__ . '/../views/inventario/index.php';
    }

    // Método para Alertas de Stock Mínimo (REQ-18) y Sugerencias de Compra (REQ-03)
    public function alertas(): void {
        $sugerencias = $this->inventarioModel->obtenerSugerenciasCompra();
        require __DIR__ . '/../views/inventario/alertas.php';
    }

    // Mostrar formulario de Transferencia entre Almacenes (REQ-22)
    public function transferir(): void {
        $almacenes = Registro::filtrarActivos((new Almacen())->listarTodos(), 'idAlmacen');
        $db = Database::conectar();
        $stmtInsumos = $db->query("SELECT idInsumo, nombre FROM insumos WHERE estado = 1 ORDER BY nombre");
        $insumos = $stmtInsumos->fetchAll();

        require __DIR__ . '/../views/inventario/transferir.php';
    }

    // Procesar la transferencia de stock (REQ-22)
    public function guardarTransferencia(): void {
        $idInsumo = (int)($_POST['idInsumo'] ?? 0);
        $idOrigen = (int)($_POST['idOrigen'] ?? 0);
        $idDestino = (int)($_POST['idDestino'] ?? 0);
        $cantidad = (float)($_POST['cantidad'] ?? 0);
        $idUsuario = $_SESSION['idUsuario'];

        if (!Registro::estaActivo('insumos', $idInsumo)
            || !Registro::estaActivo('almacenes', $idOrigen)
            || !Registro::estaActivo('almacenes', $idDestino)) {
            $_SESSION['error'] = "El insumo o alguno de los almacenes está desactivado.";
            header('Location: index.php?action=inventario.transferir');
            exit;
        }

        $exito = $this->inventarioModel->transferirStock($idInsumo, $idOrigen, $idDestino, $cantidad, $idUsuario);

        if ($exito) {
            header('Location: index.php?action=inventario.transferir&msj=transferencia_ok');
        } else {
            header('Location: index.php?action=inventario.transferir&err=error_transferencia');
        }
        exit;
    }

    public function historial(): void {
        $movimientos = $this->inventarioModel->historialTransferencias();
        require __DIR__ . '/../views/inventario/historial.php';
    }

    public function notificarAhora(): void {
        require_once __DIR__ . '/../models/EmailService.php';
        $sugerencias = $this->inventarioModel->obtenerSugerenciasCompra();
        if (EmailService::notificarResumenStockBajo($sugerencias)) {
            $_SESSION['exito'] = 'Correo de alerta enviado correctamente.';
        } else {
            $_SESSION['error'] = 'No se pudo enviar el correo: ' . EmailService::ultimoError();
        }
        header('Location: index.php?action=inventario.alertas');
        exit;
    }
}
