<?php
require_once __DIR__ . '/../config/Database.php';

class Usuario {
    private PDO $db;

    public function __construct() {
        $this->db = Database::conectar();
    }

    // Método para AuthController / Login (Mapea la 'ñ' explícitamente a pass_hash)
    public function buscarPorCorreo(string $correo): array|false {
        $sql = "SELECT u.idUsuario, u.nombre, u.correo, u.contraseña AS pass_hash, u.idRol, u.estado, 
                       COALESCE(r.nombre, 'Administrador') AS nombreRol 
                FROM usuarios u 
                LEFT JOIN roles r ON u.idRol = r.idRol 
                WHERE u.correo = :correo AND u.estado = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $usuario['contraseña'] = $usuario['pass_hash'];
        }
        return $usuario;
    }

    // Listar todos los usuarios con su respectivo rol
    public function listarTodos(): array {
        $sql = "SELECT u.idUsuario, u.nombre, u.correo, u.idRol, u.estado, 
                       COALESCE(r.nombre, 'Sin Rol') AS nombreRol 
                FROM usuarios u 
                LEFT JOIN roles r ON u.idRol = r.idRol 
                ORDER BY u.idUsuario DESC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un usuario por su clave primaria idUsuario
    public function obtenerPorId(int $idUsuario): ?array {
        $stmt = $this->db->prepare("SELECT idUsuario, nombre, correo, idRol, estado, contraseña AS pass_hash FROM usuarios WHERE idUsuario = :idUsuario");
        $stmt->execute([':idUsuario' => $idUsuario]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            $resultado['contraseña'] = $resultado['pass_hash'];
            return $resultado;
        }
        return null;
    }

    // Alias de obtenerPorId para compatibilidad con el controlador
    public function buscarPorId(int $idUsuario): ?array {
        return $this->obtenerPorId($idUsuario);
    }

    // Autenticación de inicio de sesión integrada
    public function login(string $correo, string $password): ?array {
        $usuario = $this->buscarPorCorreo($correo);

        if ($usuario) {
            // Permite validar tanto por password_verify como texto plano si llegara a requerirse
            if (password_verify($password, $usuario['contraseña']) || $password === $usuario['contraseña']) {
                return $usuario;
            }
        }
        return null;
    }

    // Verificar si un correo ya se encuentra registrado
    public function existeCorreo(string $correo, int $idUsuarioExcluir = 0): bool {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE correo = :correo AND idUsuario != :idUsuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':correo'    => $correo,
            ':idUsuario' => $idUsuarioExcluir
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Crear un nuevo usuario (CRUD Usuarios)
    public function crear(string $nombre, string $correo, string $password, int $idRol): bool {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nombre, correo, contraseña, idRol, estado) 
                VALUES (:nombre, :correo, :contrasena, :idRol, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'     => $nombre,
            ':correo'     => $correo,
            ':contrasena' => $hash,
            ':idRol'      => $idRol
        ]);
    }

    // Editar datos de usuario (CRUD Usuarios por Administrador)
    public function editar(int $idUsuario, string $nombre, string $correo, int $idRol): bool {
        $sql = "UPDATE usuarios SET nombre = :nombre, correo = :correo, idRol = :idRol WHERE idUsuario = :idUsuario";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'    => $nombre,
            ':correo'    => $correo,
            ':idRol'     => $idRol,
            ':idUsuario' => $idUsuario
        ]);
    }

    // Activar o desactivar usuario
    public function cambiarEstado(int $idUsuario, bool $estado): bool {
        $sql = "UPDATE usuarios SET estado = :estado WHERE idUsuario = :idUsuario";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':estado'    => $estado ? 1 : 0,
            ':idUsuario' => $idUsuario
        ]);
    }

    // Listar roles disponibles para formularios de selección
    public function listarRoles(): array {
        $sql = "SELECT * FROM roles ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // REQ-41: Actualizar datos personales del usuario logueado
    public function actualizarPerfil(int $idUsuario, string $nombre, string $correo): bool {
        $sql = "UPDATE usuarios SET nombre = :nombre, correo = :correo WHERE idUsuario = :idUsuario";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nombre'    => $nombre,
            ':correo'    => $correo,
            ':idUsuario' => $idUsuario
        ]);
    }

    // REQ-42: Cambiar la contraseña del usuario logueado (Recibe el hash listo y evita doble encriptación)
    public function cambiarPassword(int $idUsuario, string $hashPassword): bool {
        $sql = "UPDATE usuarios SET contraseña = :password WHERE idUsuario = :idUsuario";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password'  => $hashPassword,
            ':idUsuario' => $idUsuario
        ]);
    }

    // Alias por compatibilidad por si alguna otra parte del sistema llama a este método
    public function actualizarContraseña(int $idUsuario, string $hashPassword): bool {
        return $this->cambiarPassword($idUsuario, $hashPassword);
    }
}