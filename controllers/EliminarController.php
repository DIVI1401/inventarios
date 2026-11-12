<?php

require_once __DIR__ . '/../models/Registro.php';
require_once __DIR__ . '/../models/Log.php';

/**
 * Borrado definitivo de catálogos. Todas las rutas *.eliminar llegan aquí.
 * Solo se permite si el registro ya está desactivado y no tiene historial
 * (ver models/Registro.php).
 */
class EliminarController {

    // entidad => acción del listado al que se vuelve
    private const LISTADOS = [
        'usuarios' => 'usuarios',
        'almacenes' => 'almacenes',
        'categorias' => 'categorias',
        'insumos' => 'insumos',
        'proveedores' => 'proveedores',
        'productos' => 'productos',
    ];

    public function eliminar(string $entidad): void {
        $volver = 'index.php?action=' . (self::LISTADOS[$entidad] ?? 'dashboard');

        // Solo por POST (formulario con confirmación), nunca con un enlace
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset(self::LISTADOS[$entidad])) {
            header("Location: {$volver}");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($entidad === 'usuarios' && $id === (int)$_SESSION['idUsuario']) {
            $_SESSION['error'] = 'No puedes eliminar tu propio usuario.';
            header("Location: {$volver}");
            exit;
        }

        $resultado = Registro::eliminar($entidad, $id);

        if ($resultado['exito']) {
            Log::registrar($_SESSION['idUsuario'], 'Eliminó registro', "{$entidad} ID: {$id}");
            $_SESSION['exito'] = $resultado['mensaje'];
        } else {
            $_SESSION['error'] = $resultado['mensaje'];
        }

        header("Location: {$volver}");
        exit;
    }
}
