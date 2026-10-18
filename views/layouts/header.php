<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$rol = $_SESSION['rol'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AetherStock F&B</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <!-- Bootstrap Icons CDN (Necesario para los iconos de productos y recetas) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<div class="app-container d-flex">
    <!-- Sidebar / Menú Lateral -->
    <div class="sidebar bg-dark text-white p-3" style="min-width: 250px; min-height: 100vh; overflow-y: auto;">
        <h1 class="h3 mb-4 text-center">AetherStock</h1>
        <div class="nav flex-column">

            <a href="index.php?action=dashboard" class="nav-link text-white">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>

            <!-- ===== GESTIÓN (solo Administrador) ===== -->
            <?php if ($rol === 'Administrador'): ?>
                <div class="nav-section-label">Gestión</div>
                <a href="index.php?action=usuarios" class="nav-link text-white">Usuarios</a>
                <a href="index.php?action=almacenes" class="nav-link text-white">Almacenes</a>
                <a href="index.php?action=categorias" class="nav-link text-white">Categorías</a>
                <a href="index.php?action=insumos" class="nav-link text-white">Insumos</a>
                <a href="index.php?action=proveedores" class="nav-link text-white">Proveedores</a>

                <div class="nav-section-label">Productos y Recetas</div>
                <a href="index.php?action=productos" class="nav-link text-white">Productos y Recetas</a>
                <a href="index.php?action=productos.stock" class="nav-link text-white">Stock de Productos</a>
            <?php endif; ?>

            <!-- ===== OPERACIONES (Administrador y Bodeguero) ===== -->
            <?php if ($rol === 'Administrador' || $rol === 'Bodeguero'): ?>
                <div class="nav-section-label">Operaciones</div>
                <a href="index.php?action=compras" class="nav-link text-white">Compras</a>
                <a href="index.php?action=ventas" class="nav-link text-white">Ventas</a>
                <a href="index.php?action=mermas" class="nav-link text-white">Mermas</a>
                <a href="index.php?action=inventario.transferir" class="nav-link text-white">Transferir Stock</a>
                <a href="index.php?action=inventario.historial" class="nav-link text-white">Historial Transferencias</a>
            <?php endif; ?>

            <!-- ===== INVENTARIO (todos los roles) ===== -->
            <div class="nav-section-label">Inventario</div>
            <a href="index.php?action=inventario" class="nav-link text-white">Inventario</a>
            <a href="index.php?action=inventario.alertas" class="nav-link text-white">Alertas y Sugerencias</a>

            <!-- ===== REPORTES (solo Administrador) ===== -->
            <?php if ($rol === 'Administrador'): ?>
                <div class="nav-section-label">Reportes</div>
                <a href="index.php?action=reportes.ventas" class="nav-link text-white">Reporte Ventas</a>
                <a href="index.php?action=reportes.comprasProveedor" class="nav-link text-white">Reporte Compras</a>
                <a href="index.php?action=reportes.mermas" class="nav-link text-white">Reporte Mermas</a>
                <a href="index.php?action=precios.historial" class="nav-link text-white">Historial Precios</a>
                <a href="index.php?action=precios.comparativa" class="nav-link text-white">Comparar Precios</a>
                <a href="index.php?action=reportes.auditoria" class="nav-link text-white">Log Auditoría</a>
                <a href="index.php?action=reportes.exportarExcel" class="nav-link text-white">Exportar Inventario</a>
            <?php endif; ?>

        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="main-content flex-grow-1">
        <!-- Topbar / Barra Superior -->
        <div class="topbar bg-light p-3 border-bottom d-flex justify-content-between align-items-center">
            <div class="usuario-info">
                <strong><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></strong> 
                <span class="badge bg-secondary ms-2"><?= htmlspecialchars($rol) ?></span>
            </div>
            <div>
                <!-- REQ-41/REQ-42: Acceso a Mi Perfil -->
                <a href="index.php?action=perfil" class="btn btn-outline-primary btn-sm me-2">Mi Perfil</a>
                <a href="index.php?action=logout" class="btn btn-outline-danger btn-sm">Cerrar sesión</a>
            </div>
        </div>
        <div class="content p-4">