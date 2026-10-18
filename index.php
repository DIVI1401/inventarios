<?php

session_start();

// Habilitar visualización de errores temporalmente para diagnóstico
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/middleware.php';
require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/AlmacenController.php';
require_once __DIR__ . '/controllers/InsumoController.php';
require_once __DIR__ . '/controllers/ProveedorController.php';
require_once __DIR__ . '/controllers/CompraController.php';
require_once __DIR__ . '/controllers/MermaController.php';
require_once __DIR__ . '/controllers/DashboardController.php';
require_once __DIR__ . '/controllers/InventarioController.php';
require_once __DIR__ . '/controllers/ReporteController.php';
require_once __DIR__ . '/controllers/CategoriaController.php';
require_once __DIR__ . '/controllers/ProductoController.php'; 
require_once __DIR__ . '/controllers/VentaController.php';

$action = $_GET['action'] ?? 'login';
$auth = new AuthController();

switch ($action) {
    // ===== AUTENTICACIÓN =====
    case 'login':
        $auth->mostrarLogin();
        break;

    case 'doLogin':
        $auth->login();
        break;

    case 'logout':
        $auth->logout();
        break;

    case 'dashboard':
        requiereLogin();
        (new DashboardController())->mostrar();
        break;

    // ===== PERFIL Y AUTOSERVICIO (REQ-41, REQ-42) =====
    case 'perfil':
        requiereLogin();
        (new UsuarioController())->perfil();
        break;

    case 'guardarPerfil':
        requiereLogin();
        (new UsuarioController())->guardarPerfil();
        break;

    case 'cambiarPassword':
        requiereLogin();
        (new UsuarioController())->cambiarPassword();
        break;

    // ===== USUARIOS =====
    case 'usuarios':
        requiereRol(['Administrador']);
        (new UsuarioController())->listar();
        break;

    case 'usuarios.crear':
        requiereRol(['Administrador']);
        (new UsuarioController())->mostrarFormularioCrear();
        break;

    case 'usuarios.guardar':
        requiereRol(['Administrador']);
        (new UsuarioController())->crear();
        break;

    case 'usuarios.editar':
        requiereRol(['Administrador']);
        (new UsuarioController())->mostrarFormularioEditar();
        break;

    case 'usuarios.actualizar':
        requiereRol(['Administrador']);
        (new UsuarioController())->editar();
        break;

    case 'usuarios.desactivar':
        requiereRol(['Administrador']);
        (new UsuarioController())->desactivar();
        break;

    case 'usuarios.activar':
        requiereRol(['Administrador']);
        (new UsuarioController())->activar();
        break;

    // ===== ALMACENES =====
    case 'almacenes':
        requiereRol(['Administrador']);
        (new AlmacenController())->listar();
        break;

    case 'almacenes.crear':
        requiereRol(['Administrador']);
        (new AlmacenController())->mostrarFormularioCrear();
        break;

    case 'almacenes.guardar':
        requiereRol(['Administrador']);
        (new AlmacenController())->crear();
        break;

    case 'almacenes.editar':
        requiereRol(['Administrador']);
        (new AlmacenController())->mostrarFormularioEditar();
        break;

    case 'almacenes.actualizar':
        requiereRol(['Administrador']);
        (new AlmacenController())->editar();
        break;

    case 'almacenes.desactivar':
        requiereRol(['Administrador']);
        (new AlmacenController())->desactivar();
        break;

    case 'almacenes.activar':
        requiereRol(['Administrador']);
        (new AlmacenController())->activar();
        break;

    // ===== INSUMOS =====
    case 'insumos':
        requiereRol(['Administrador']);
        (new InsumoController())->listar();
        break;

    case 'insumos.crear':
        requiereRol(['Administrador']);
        (new InsumoController())->mostrarFormularioCrear();
        break;

    case 'insumos.guardar':
        requiereRol(['Administrador']);
        (new InsumoController())->crear();
        break;

    case 'insumos.editar':
        requiereRol(['Administrador']);
        (new InsumoController())->mostrarFormularioEditar();
        break;

    case 'insumos.actualizar':
        requiereRol(['Administrador']);
        (new InsumoController())->editar();
        break;

    case 'insumos.desactivar':
        requiereRol(['Administrador']);
        (new InsumoController())->desactivar();
        break;

    case 'insumos.activar':
        requiereRol(['Administrador']);
        (new InsumoController())->activar();
        break;

    // ===== PROVEEDORES =====
    case 'proveedores':
        requiereRol(['Administrador']);
        (new ProveedorController())->listar();
        break;

    case 'proveedores.crear':
        requiereRol(['Administrador']);
        (new ProveedorController())->mostrarFormularioCrear();
        break;

    case 'proveedores.guardar':
        requiereRol(['Administrador']);
        (new ProveedorController())->crear();
        break;

    case 'proveedores.editar':
        requiereRol(['Administrador']);
        (new ProveedorController())->mostrarFormularioEditar();
        break;

    case 'proveedores.actualizar':
        requiereRol(['Administrador']);
        (new ProveedorController())->editar();
        break;

    case 'proveedores.desactivar':
        requiereRol(['Administrador']);
        (new ProveedorController())->desactivar();
        break;

    case 'proveedores.activar':
        requiereRol(['Administrador']);
        (new ProveedorController())->activar();
        break;

    // ===== COMPRAS =====
    case 'compras':
        requiereRol(['Administrador', 'Bodeguero']);
        (new CompraController())->listar();
        break;

    case 'compras.crear':
        requiereRol(['Administrador', 'Bodeguero']);
        (new CompraController())->mostrarFormularioCrear();
        break;

    case 'compras.guardar':
        requiereRol(['Administrador', 'Bodeguero']);
        (new CompraController())->crear();
        break;

    case 'compras.detalle':
        requiereRol(['Administrador', 'Bodeguero']);
        (new CompraController())->verDetalle();
        break;

    // ===== MERMAS =====
    case 'mermas':
        requiereRol(['Administrador', 'Bodeguero']);
        (new MermaController())->listar();
        break;

    case 'mermas.crear':
        requiereRol(['Administrador', 'Bodeguero']);
        (new MermaController())->mostrarFormularioCrear();
        break;

    case 'mermas.guardar':
        requiereRol(['Administrador', 'Bodeguero']);
        (new MermaController())->crear();
        break;

    // ===== INVENTARIO, ALERTAS Y TRANSFERENCIAS =====
    case 'inventario':
        requiereLogin();
        (new InventarioController())->listar();
        break;

    case 'inventario.alertas':
        requiereLogin();
        (new InventarioController())->alertas();
        break;

    case 'inventario.transferir':
        requiereRol(['Administrador', 'Bodeguero']);
        (new InventarioController())->transferir();
        break;

    case 'inventario.guardarTransferencia':
        requiereRol(['Administrador', 'Bodeguero']);
        (new InventarioController())->guardarTransferencia();
        break;

    case 'inventario.historial':
        requiereLogin();
        (new InventarioController())->historial();
        break;

    // ===== REPORTES Y MÓDULOS AVANZADOS (REQ-43, REQ-39, REQ-40, REQ-47) =====
    case 'reportes.mermas':
        requiereRol(['Administrador', 'Bodeguero']);
        (new ReporteController())->mermas();
        break;

    case 'reportes.comprasProveedor':
        requiereRol(['Administrador']);
        (new ReporteController())->comprasProveedor();
        break;

    case 'reportes.auditoria':
        requiereRol(['Administrador']);
        (new ReporteController())->auditoria();
        break;

    case 'reportes.exportarExcel':
        requiereLogin();
        (new ReporteController())->exportarExcel();
        break;

    case 'precios.historial':
        requiereRol(['Administrador', 'Bodeguero']);
        (new InsumoController())->historialPrecios();
        break;

    case 'precios.comparativa':
        requiereRol(['Administrador', 'Bodeguero']);
        (new InsumoController())->comparativaPrecios();
        break;
    
    case 'inventario.notificarAhora':
        requiereRol(['Administrador']);
        (new InventarioController())->notificarAhora();
        break;

    // ===== CATEGORÍAS =====
    case 'categorias':
        requiereRol(['Administrador']);
        (new CategoriaController())->listar();
        break;

    case 'categorias.crear':
        requiereRol(['Administrador']);
        (new CategoriaController())->mostrarFormularioCrear();
        break;

    case 'categorias.guardar':
        requiereRol(['Administrador']);
        (new CategoriaController())->crear();
        break;

    case 'categorias.editar':
        requiereRol(['Administrador']);
        (new CategoriaController())->mostrarFormularioEditar();
        break;

    case 'categorias.actualizar':
        requiereRol(['Administrador']);
        (new CategoriaController())->editar();
        break;

    case 'categorias.desactivar':
        requiereRol(['Administrador']);
        (new CategoriaController())->desactivar();
        break;

    case 'categorias.activar':
        requiereRol(['Administrador']);
        (new CategoriaController())->activar();
        break;

    // ===== PRODUCTOS Y RECETAS (REQ-1, REQ-2, REQ-3, REQ-4, REQ-26, REQ-27) =====
    case 'productos':
        requiereRol(['Administrador']);
        (new ProductoController())->listar();
        break;

    case 'productos.crear':
        requiereRol(['Administrador']);
        (new ProductoController())->mostrarFormularioCrear();
        break;

    case 'productos.guardar':
        requiereRol(['Administrador']);
        (new ProductoController())->crear();
        break;

    case 'productos.editar':
        requiereRol(['Administrador']);
        (new ProductoController())->mostrarFormularioEditar();
        break;

    case 'productos.actualizar':
        requiereRol(['Administrador']);
        (new ProductoController())->editar();
        break;

    case 'productos.estado':
        requiereRol(['Administrador']);
        (new ProductoController())->cambiarEstado();
        break;

    case 'productos.receta':
        requiereRol(['Administrador']);
        (new ProductoController())->gestionarReceta();
        break;

    case 'productos.guardarReceta':
        requiereRol(['Administrador']);
        (new ProductoController())->guardarReceta();
        break;

    case 'productos.eliminarReceta':
        requiereRol(['Administrador']);
        (new ProductoController())->eliminarReceta();
        break;

    case 'productos.stock':
        requiereLogin();
        (new ProductoController())->stockDisponible();
        break;

    case 'ventas':
    requiereRol(['Administrador', 'Bodeguero']);
    (new VentaController())->listar();
    break;

    case 'ventas.crear':
        requiereRol(['Administrador', 'Bodeguero']);
        (new VentaController())->mostrarFormularioCrear();
        break;

    case 'ventas.guardar':
        requiereRol(['Administrador', 'Bodeguero']);
        (new VentaController())->crear();
        break;

    case 'ventas.detalle':
        requiereRol(['Administrador', 'Bodeguero']);
        (new VentaController())->verDetalle();
        break;

    case 'ventas.comprobante':
        requiereRol(['Administrador', 'Bodeguero']);
        (new VentaController())->comprobante();
        break;

    case 'reportes.ventas':
        requiereRol(['Administrador']);
        (new ReporteController())->ventas();
        break;

    case 'reportes.ventasExcel':
        requiereRol(['Administrador']);
        (new ReporteController())->exportarVentasExcel();
        break;

    default:
        $auth->mostrarLogin();

    
}