<?php

class Database {
    private static ?PDO $instancia = null;

    private function __construct() {}

    public static function conectar(): PDO {
        if (self::$instancia === null) {
            // En Render se usa DATABASE_URL (postgres://usuario:clave@host:puerto/base)
            $url = getenv('DATABASE_URL');
            if ($url) {
                $partes = parse_url($url);
                $host = $partes['host'];
                $puerto = $partes['port'] ?? '5432';
                $dbName = ltrim($partes['path'], '/');
                $usuario = urldecode($partes['user']);
                $password = urldecode($partes['pass'] ?? '');
            } else {
                $host = getenv('DB_HOST') ?: '127.0.0.1';
                $puerto = getenv('DB_PORT') ?: '5432';
                $dbName = getenv('DB_NAME') ?: 'aetherstock';
                $usuario = getenv('DB_USER') ?: 'postgres';
                $password = getenv('DB_PASSWORD') ?: '';
            }
            $sslmode = getenv('DB_SSLMODE') ?: 'prefer';

            $dsn = "pgsql:host={$host};port={$puerto};dbname={$dbName};sslmode={$sslmode}";

            try {
                self::$instancia = new PDO($dsn, $usuario, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // Igual que pdo_mysql: los parámetros se envían como literales y
                    // PostgreSQL los convierte al tipo de la columna
                    PDO::ATTR_EMULATE_PREPARES => true,
                    PDO::ATTR_STATEMENT_CLASS => [PgStatement::class],
                ]);
                self::$instancia->exec("SET client_encoding TO 'UTF8'");
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }

        return self::$instancia;
    }
}

/**
 * PostgreSQL devuelve los nombres de columna en minúsculas (idusuario),
 * pero el código usa camelCase (idUsuario). Esta clase restaura las claves.
 */
class PgStatement extends PDOStatement {
    private const CLAVES = [
        'idRol', 'idUsuario', 'idAlmacen', 'idCategoria', 'idProveedor', 'idInsumo',
        'idInventario', 'idCompra', 'idDetalleCompra', 'idMerma', 'idTransferencia',
        'idLog', 'idProducto', 'idReceta', 'idRecetaInsumo', 'idVenta', 'idDetalleVenta',
        'idAlmacenOrigen', 'idAlmacenDestino', 'unidadMedida', 'precioUnitario',
        'fechaVencimiento', 'fechaActualizacion', 'cantidadDisponible', 'stockMinimo',
        'fechaCompra', 'fechaMerma', 'fechaVenta', 'metodoPago', 'montoRecibido',
        'nombreRol', 'nombreCategoria', 'nombreProveedor', 'nombreAlmacen', 'nombreInsumo',
        'nombreUsuario', 'nombreProducto', 'ubicacionAlmacen', 'totalItems', 'gastoTotal',
        'vecesComprado', 'precioPromedio', 'precioMinimo', 'precioMaximo', 'ultimaCompra',
        'ultimaFecha', 'costoTotal', 'cantidadRequerida', 'numVentas', 'totalVendido',
        'ticketPromedio', 'unidadesVendidas',
    ];

    private static ?array $mapa = null;

    protected function __construct() {}

    private static function restaurar(mixed $fila): mixed {
        if (!is_array($fila)) {
            return $fila;
        }
        if (self::$mapa === null) {
            self::$mapa = array_combine(array_map('strtolower', self::CLAVES), self::CLAVES);
        }
        $resultado = [];
        foreach ($fila as $clave => $valor) {
            $resultado[is_string($clave) ? (self::$mapa[$clave] ?? $clave) : $clave] = $valor;
        }
        return $resultado;
    }

    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed {
        return self::restaurar(parent::fetch($mode, $cursorOrientation, $cursorOffset));
    }

    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array {
        $filas = parent::fetchAll($mode, ...$args);
        if ($mode === PDO::FETCH_COLUMN) {
            return $filas;
        }
        return array_map([self::class, 'restaurar'], $filas);
    }
}
