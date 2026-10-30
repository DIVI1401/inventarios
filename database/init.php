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
