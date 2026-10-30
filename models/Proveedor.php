<?php

require_once __DIR__ . '/../config/Database.php';

class Proveedor {
    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    public function listarTodos(): array {
        $stmt = $this->db->query("SELECT * FROM proveedores ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE idProveedor = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function crear(string $nombre, string $contacto, string $telefono, string $correo, string $direccion): bool {
        $stmt = $this->db->prepare(
            "INSERT INTO proveedores (nombre, contacto, telefono, correo, direccion) 
             VALUES (:nombre, :contacto, :telefono, :correo, :direccion)"
        );
        return $stmt->execute([
            'nombre' => $nombre,
            'contacto' => $contacto,
            'telefono' => $telefono,
            'correo' => $correo,
            'direccion' => $direccion,
        ]);
    }

    public function editar(int $id, string $nombre, string $contacto, string $telefono, string $correo, string $direccion): bool {
        $stmt = $this->db->prepare(
            "UPDATE proveedores SET nombre = :nombre, contacto = :contacto, telefono = :telefono,
             correo = :correo, direccion = :direccion
             WHERE idProveedor = :id"
        );
        return $stmt->execute([
            'nombre' => $nombre,
            'contacto' => $contacto,
            'telefono' => $telefono,
            'correo' => $correo,
            'direccion' => $direccion,
            'id' => $id,
        ]);
    }

    public function cambiarEstado(int $id, bool $estado): bool {
        $stmt = $this->db->prepare("UPDATE proveedores SET estado = :estado WHERE idProveedor = :id");
        return $stmt->execute(['estado' => $estado ? 1 : 0, 'id' => $id]);
    }

    // Requisito 40: comparar precios de compra entre proveedores de un mismo insumo
    public function historialPrecioPorInsumo(int $idInsumo): array {
        $stmt = $this->db->prepare(
            "SELECT p.nombre AS nombreProveedor, dc.precioUnitario, c.fechaCompra
             FROM detalle_compras dc
             INNER JOIN compras c ON dc.idCompra = c.idCompra
             INNER JOIN proveedores p ON c.idProveedor = p.idProveedor
             WHERE dc.idInsumo = :idInsumo
             ORDER BY c.fechaCompra DESC"
        );
        $stmt->execute(['idInsumo' => $idInsumo]);
        return $stmt->fetchAll();
    }


    public function validar(string $nombre, string $contacto, string $telefono, string $correo): array {
    $errores = [];

    if (empty(trim($nombre))) {
        $errores[] = "El nombre es obligatorio.";
    } elseif (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $nombre)) {
        $errores[] = "El nombre solo puede contener letras y espacios.";
    }

    if (!empty($contacto) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $contacto)) {
        $errores[] = "El contacto solo puede contener letras y espacios.";
    }

    if (!empty($telefono) && !preg_match('/^[0-9]{7,15}$/', $telefono)) {
        $errores[] = "El teléfono solo puede contener números (entre 7 y 15 dígitos).";
    }

    if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo no tiene un formato válido.";
    }

    return $errores;
}
}