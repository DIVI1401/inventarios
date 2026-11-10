<?php
// Se ejecuta al arrancar el contenedor en Render (ver Dockerfile).
// Crea las tablas si la base está vacía y, si no hay usuarios, crea un
// administrador con las variables ADMIN_NOMBRE / ADMIN_EMAIL / ADMIN_PASSWORD.

require_once __DIR__ . '/../config/Database.php';

$db = Database::conectar();

$existe = $db->query("SELECT to_regclass('public.usuarios') IS NOT NULL")->fetchColumn();
if (!$existe) {
    $db->exec(file_get_contents(__DIR__ . '/schema.sql'));
    echo "[init] Esquema creado.\n";
} else {
    echo "[init] Esquema ya existe.\n";
}

// ---------------------------------------------------------------------
// Migraciones: cambios de estructura para bases que ya existían.
// Cada una es idempotente (se puede ejecutar en cada arranque sin daño).
// ---------------------------------------------------------------------
function columnaExiste(PDO $db, string $tabla, string $columna): bool {
    $stmt = $db->prepare(
        "SELECT 1 FROM information_schema.columns
         WHERE table_schema = 'public' AND table_name = ? AND column_name = ?"
    );
    $stmt->execute([strtolower($tabla), strtolower($columna)]);
    return (bool)$stmt->fetchColumn();
}

// 1. La fecha de vencimiento pasa del insumo a cada lote comprado
if (!columnaExiste($db, 'detalle_compras', 'fechaVencimiento')) {
    $db->exec("ALTER TABLE detalle_compras ADD COLUMN fechaVencimiento DATE");
    echo "[init] Migración: detalle_compras.fechaVencimiento agregada.\n";
}

// 2. Precio de venta automático según margen de ganancia
if (!columnaExiste($db, 'productos', 'margenGanancia')) {
    $db->exec("ALTER TABLE productos ALTER COLUMN precio SET DEFAULT 0");
    $db->exec("ALTER TABLE productos ADD COLUMN margenGanancia NUMERIC(5,2) NOT NULL DEFAULT 70
               CHECK (margenGanancia >= 0 AND margenGanancia <= 95)");
    require_once __DIR__ . '/../models/Producto.php';
    (new Producto())->recalcularPrecios();
    echo "[init] Migración: productos.margenGanancia agregada y precios recalculados.\n";
}

$totalUsuarios = (int)$db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$correo = getenv('ADMIN_EMAIL');
$password = getenv('ADMIN_PASSWORD');

if ($totalUsuarios === 0 && $correo && $password) {
    $stmt = $db->prepare(
        "INSERT INTO usuarios (nombre, correo, contraseña, idRol, estado) VALUES (?, ?, ?, 1, 1)"
    );
    $stmt->execute([getenv('ADMIN_NOMBRE') ?: 'Administrador', $correo, password_hash($password, PASSWORD_DEFAULT)]);
    echo "[init] Administrador {$correo} creado.\n";
}
