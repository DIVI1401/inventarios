<?php

require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Insumo.php';
require_once __DIR__ . '/../models/Receta.php';
require_once __DIR__ . '/../models/Log.php';
require_once __DIR__ . '/../models/Almacen.php';
require_once __DIR__ . '/../models/Registro.php';
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
        $categorias = Registro::filtrarActivos($this->categoriaModel->listarTodas(), 'idCategoria');
        require __DIR__ . '/../views/productos/crear.php';
    }

    public function crear(): void {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $margen = floatval($_POST['margenGanancia'] ?? Producto::MARGEN_POR_DEFECTO);
        $idCategoria = !empty($_POST['idCategoria']) ? (int)$_POST['idCategoria'] : null;

        if (empty($nombre)) {
            $_SESSION['error'] = "El nombre del producto es obligatorio.";
            header("Location: index.php?action=productos.crear");
            exit;
        }
        if ($idCategoria && !Registro::estaActivo('categorias', $idCategoria)) {
            $_SESSION['error'] = "La categoría seleccionada está desactivada.";
            header("Location: index.php?action=productos.crear");
            exit;
        }
        if ($margen < 0 || $margen > Producto::MARGEN_MAXIMO) {
            $_SESSION['error'] = "El margen de ganancia debe estar entre 0 % y " . Producto::MARGEN_MAXIMO . " %.";
            header("Location: index.php?action=productos.crear");
            exit;
        }

        $idProducto = $this->productoModel->crear($nombre, $descripcion, $margen, $idCategoria);
        Log::registrar($_SESSION['idUsuario'], 'Creó producto', "Producto ID: {$idProducto} - {$nombre}");

        // Siguiente paso natural: armar la receta, que es la que define el precio
        $_SESSION['exito'] = "Producto creado. Ahora agrega los insumos de la receta para calcular su precio de venta.";
        header("Location: index.php?action=productos.receta&id={$idProducto}");
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

        $categorias = Registro::filtrarActivos($this->categoriaModel->listarTodas(), 'idCategoria', [(int)$producto['idCategoria']]);
        $receta = $this->recetaModel->obtenerPorProducto($idProducto);
        $costoReceta = $receta ? $this->recetaModel->calcularCosto($receta['idReceta']) : 0;
        require __DIR__ . '/../views/productos/editar.php';
    }

    public function editar(): void {
        $idProducto = (int)($_POST['idProducto'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $margen = floatval($_POST['margenGanancia'] ?? Producto::MARGEN_POR_DEFECTO);
        $idCategoria = !empty($_POST['idCategoria']) ? (int)$_POST['idCategoria'] : null;
        $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : 1;

        if ($idProducto <= 0 || empty($nombre) || $margen < 0 || $margen > Producto::MARGEN_MAXIMO) {
            $_SESSION['error'] = "El nombre es obligatorio y el margen debe estar entre 0 % y " . Producto::MARGEN_MAXIMO . " %.";
            header("Location: index.php?action=productos.editar&id={$idProducto}");
            exit;
        }

        $actual = $this->productoModel->obtenerPorId($idProducto);
        if ($idCategoria && $idCategoria !== (int)($actual['idCategoria'] ?? 0) && !Registro::estaActivo('categorias', $idCategoria)) {
            $_SESSION['error'] = "La categoría seleccionada está desactivada.";
            header("Location: index.php?action=productos.editar&id={$idProducto}");
            exit;
        }

        $this->productoModel->actualizar($idProducto, $nombre, $descripcion, $margen, $idCategoria, $estado);
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
        $todosInsumos = array_values(array_filter(
            $this->insumoModel->listarTodos(),
            fn($i) => (int)$i['estado'] === 1 || in_array($i['idInsumo'], array_column($insumosReceta, 'idInsumo'))
        ));

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

        // Los insumos nuevos de la receta deben estar activos (los que ya tenía se conservan)
        $recetaActual = $this->recetaModel->obtenerPorProducto($idProducto);
        $yaEnReceta = $recetaActual
            ? array_map('intval', array_column($this->recetaModel->obtenerInsumosDeReceta($recetaActual['idReceta']), 'idInsumo'))
            : [];
        $nuevos = array_diff(array_map('intval', array_filter($insumos)), $yaEnReceta);
        if (!Registro::todosActivos('insumos', $nuevos)) {
            $_SESSION['error'] = "La receta incluye un insumo desactivado.";
            header("Location: index.php?action=productos.receta&id={$idProducto}");
            exit;
        }

        $ok = $this->recetaModel->guardarReceta($idProducto, $instrucciones, $insumos, $cantidades);

        if ($ok) {
            $this->productoModel->recalcularPrecios(idProducto: $idProducto);
            $producto = $this->productoModel->obtenerPorId($idProducto);
            Log::registrar($_SESSION['idUsuario'], 'Guardó receta', "Receta asociada al producto ID: {$idProducto}");
            $_SESSION['exito'] = "Receta guardada. Precio de venta calculado: $" . number_format((float)$producto['precio'], 0, ',', '.');
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
        $almacenes = Registro::filtrarActivos((new Almacen())->listarTodos(), 'idAlmacen');
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