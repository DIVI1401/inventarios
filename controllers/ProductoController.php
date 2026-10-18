<?php

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Insumo.php';
require_once __DIR__ . '/../models/Receta.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../models/Almacen.php';
class ProductoController {
    private Producto $productoModel;
    private Categoria $categoriaModel;
    private Insumo $insumoModel;
    private Receta $recetaModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->productoModel = new Producto();
        $this->categoriaModel = new Categoria();
        $this->insumoModel = new Insumo();
        $this->recetaModel = new Receta();
    }

    public function listar(): void {
        $productos = $this->productoModel->listarTodos();
        require __DIR__ . '/../views/productos/index.php';
    }

    public function mostrarFormularioCrear(): void {
        $categorias = $this->categoriaModel->listarTodas();
        require __DIR__ . '/../views/productos/crear.php';
    }

    public function crear(): void {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $idCategoria = !empty($_POST['idCategoria']) ? (int)$_POST['idCategoria'] : null;

        if (empty($nombre) || $precio <= 0) {
            $_SESSION['error'] = "El nombre y un precio válido son obligatorios.";
            header("Location: index.php?action=productos.crear");
            exit;
        }

        $idProducto = $this->productoModel->crear($nombre, $descripcion, $precio, $idCategoria);
        Log::registrar($_SESSION['idUsuario'], 'Creó producto', "Producto ID: {$idProducto} - {$nombre}");

        $_SESSION['exito'] = "Producto creado exitosamente.";
        header("Location: index.php?action=productos");
        exit;
    }

    public function mostrarFormularioEditar(): void {
        $idProducto = (int)($_GET['id'] ?? 0);
        $producto = $this->productoModel->obtenerPorId($idProducto);

        if (!$producto) {
            $_SESSION['error'] = "Producto no encontrado.";
            header("Location: index.php?action=productos");
            exit;
        }

        $categorias = $this->categoriaModel->listarTodas();
        require __DIR__ . '/../views/productos/editar.php';
    }

    public function editar(): void {
        $idProducto = (int)($_POST['idProducto'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $idCategoria = !empty($_POST['idCategoria']) ? (int)$_POST['idCategoria'] : null;
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

        if ($idProducto <= 0 || empty($nombre) || $precio <= 0) {
            $_SESSION['error'] = "Todos los campos son obligatorios y el precio debe ser mayor a 0.";
            header("Location: index.php?action=productos.editar&id={$idProducto}");
            exit;
        }

        $this->productoModel->actualizar($idProducto, $nombre, $descripcion, $precio, $idCategoria, $estado);
        Log::registrar($_SESSION['idUsuario'], 'Actualizó producto', "Producto ID: {$idProducto}");

        $_SESSION['exito'] = "Producto actualizado correctamente.";
        header("Location: index.php?action=productos");
        exit;
    }

    public function cambiarEstado(): void {
        $idProducto = (int)($_GET['id'] ?? 0);
        $estado = (int)($_GET['estado'] ?? 0);

        if ($idProducto > 0) {
            $this->productoModel->cambiarEstado($idProducto, $estado);
            Log::registrar($_SESSION['idUsuario'], 'Cambió estado de producto', "Producto ID: {$idProducto} a estado {$estado}");
            $_SESSION['exito'] = "Estado del producto actualizado.";
        }

        header("Location: index.php?action=productos");
        exit;
    }

    // Módulo de Receta asociada a Producto
    public function gestionarReceta(): void {
        $idProducto = (int)($_GET['id'] ?? 0);
        $producto = $this->productoModel->obtenerPorId($idProducto);

        if (!$producto) {
            $_SESSION['error'] = "Producto no encontrado.";
            header("Location: index.php?action=productos");
            exit;
        }

        $receta = $this->recetaModel->obtenerPorProducto($idProducto);
        $insumosReceta = $receta ? $this->recetaModel->obtenerInsumosDeReceta($receta['idReceta']) : [];
        $costoReceta = $receta ? $this->recetaModel->calcularCosto($receta['idReceta']) : 0;
        $todosInsumos = $this->insumoModel->listarTodos();

        require __DIR__ . '/../views/productos/receta.php';
    }

    public function guardarReceta(): void {
        $idProducto = (int)($_POST['idProducto'] ?? 0);
        $instrucciones = trim($_POST['instrucciones'] ?? '');
        $insumos = $_POST['insumos'] ?? [];
        $cantidades = $_POST['cantidades'] ?? [];

        if ($idProducto <= 0) {
            $_SESSION['error'] = "Producto inválido.";
            header("Location: index.php?action=productos");
            exit;
        }

        $ok = $this->recetaModel->guardarReceta($idProducto, $instrucciones, $insumos, $cantidades);

        if ($ok) {
            Log::registrar($_SESSION['idUsuario'], 'Guardó receta', "Receta asociada al producto ID: {$idProducto}");
            $_SESSION['exito'] = "Receta guardada exitosamente.";
        } else {
            $_SESSION['error'] = "Error al guardar la receta.";
        }

        header("Location: index.php?action=productos.receta&id={$idProducto}");
        exit;
    }

        public function eliminarReceta(): void {
        $idProducto = (int)($_GET['id'] ?? 0);
        $receta = $this->recetaModel->obtenerPorProducto($idProducto);

        if ($receta) {
            $this->recetaModel->eliminarReceta($receta['idReceta']);
            Log::registrar($_SESSION['idUsuario'], 'Eliminó receta', "Producto ID: {$idProducto}");
            $_SESSION['exito'] = "Receta eliminada correctamente.";
        }

        header("Location: index.php?action=productos");
        exit;
    }

        public function stockDisponible(): void {
        $idAlmacen = (int)($_GET['idAlmacen'] ?? 0);
        $almacenes = (new Almacen())->listarTodos();
        $productos = $this->productoModel->listarTodos();

        $stockPorProducto = [];
        if ($idAlmacen) {
            foreach ($productos as $p) {
                $receta = $this->recetaModel->obtenerPorProducto($p['idProducto']);
                $stockPorProducto[$p['idProducto']] = $receta 
                    ? $this->recetaModel->calcularStockDisponible($receta['idReceta'], $idAlmacen) 
                    : null; // null = producto sin receta todavía
            }
        }

        require __DIR__ . '/../views/productos/stock.php';
    }
}