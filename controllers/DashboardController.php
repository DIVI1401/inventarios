<?php

require_once __DIR__ . '/../models/Inventario.php';
require_once __DIR__ . '/../models/Insumo.php';
require_once __DIR__ . '/../models/Compra.php';

class DashboardController {

    public function mostrar(): void {
        $inventarioModel = new Inventario();
        $insumoModel = new Insumo();
        $compraModel = new Compra();

        $stockBajo = $inventarioModel->stockBajo();
        $proximosAVencer = $insumoModel->proximosAVencer(7);
        $gastoDelMes = $compraModel->gastoDelMes();

        require __DIR__ . '/../views/dashboard/index.php';
    }
}