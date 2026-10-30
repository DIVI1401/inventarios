-- =====================================================================
-- AetherStock - Esquema para PostgreSQL (Render)
-- Convertido desde el volcado MySQL/MariaDB original.
--
-- Nota: los nombres de columnas van SIN comillas, así PostgreSQL los
-- guarda en minúsculas (idusuario, fechacompra...). Las consultas del
-- código PHP tampoco usan comillas, así que coinciden; config/Database.php
-- devuelve las claves en camelCase para que las vistas sigan funcionando.
-- =====================================================================

CREATE TABLE IF NOT EXISTS roles (
    idRol        SERIAL PRIMARY KEY,
    nombre       VARCHAR(50)  NOT NULL UNIQUE,
    descripcion  VARCHAR(150)
);

CREATE TABLE IF NOT EXISTS usuarios (
    idUsuario    SERIAL PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL,
    correo       VARCHAR(100) NOT NULL UNIQUE,
    contraseña   VARCHAR(255) NOT NULL,
    idRol        INTEGER      NOT NULL REFERENCES roles (idRol),
    estado       SMALLINT     NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS almacenes (
    idAlmacen    SERIAL PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL,
    ubicacion    VARCHAR(150) NOT NULL,
    descripcion  VARCHAR(255),
    estado       SMALLINT     NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS categorias (
    idCategoria  SERIAL PRIMARY KEY,
    nombre       VARCHAR(50)  NOT NULL,
    descripcion  VARCHAR(150),
    estado       SMALLINT     NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS proveedores (
    idProveedor  SERIAL PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL,
    contacto     VARCHAR(100),
    telefono     VARCHAR(20),
    correo       VARCHAR(100),
    direccion    VARCHAR(150),
    estado       SMALLINT     NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS insumos (
    idInsumo          SERIAL PRIMARY KEY,
    nombre            VARCHAR(100)  NOT NULL,
    unidadMedida      VARCHAR(30)   NOT NULL,
    precioUnitario    NUMERIC(10,2) NOT NULL DEFAULT 0,
    fechaVencimiento  DATE,
    idCategoria       INTEGER REFERENCES categorias (idCategoria),
    estado            SMALLINT      NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS inventarios (
    idInventario        SERIAL PRIMARY KEY,
    idAlmacen           INTEGER       NOT NULL REFERENCES almacenes (idAlmacen),
    idInsumo            INTEGER       NOT NULL REFERENCES insumos (idInsumo),
    fechaActualizacion  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cantidadDisponible  NUMERIC(10,2) NOT NULL DEFAULT 0,
    stockMinimo         NUMERIC(10,2) NOT NULL DEFAULT 0,
    CONSTRAINT uk_almacen_insumo UNIQUE (idAlmacen, idInsumo)
);

CREATE TABLE IF NOT EXISTS compras (
    idCompra     SERIAL PRIMARY KEY,
    idProveedor  INTEGER       NOT NULL REFERENCES proveedores (idProveedor),
    idAlmacen    INTEGER       NOT NULL REFERENCES almacenes (idAlmacen),
    idUsuario    INTEGER       NOT NULL REFERENCES usuarios (idUsuario),
    fechaCompra  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total        NUMERIC(10,2) NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS detalle_compras (
    idDetalleCompra  SERIAL PRIMARY KEY,
    idCompra         INTEGER       NOT NULL REFERENCES compras (idCompra) ON DELETE CASCADE,
    idInsumo         INTEGER       NOT NULL REFERENCES insumos (idInsumo),
    cantidad         NUMERIC(10,2) NOT NULL,
    precioUnitario   NUMERIC(10,2) NOT NULL,
    subtotal         NUMERIC(10,2) GENERATED ALWAYS AS (cantidad * precioUnitario) STORED
);

CREATE TABLE IF NOT EXISTS mermas (
    idMerma     SERIAL PRIMARY KEY,
    idInsumo    INTEGER       NOT NULL REFERENCES insumos (idInsumo),
    idAlmacen   INTEGER       NOT NULL REFERENCES almacenes (idAlmacen),
    idUsuario   INTEGER       NOT NULL REFERENCES usuarios (idUsuario),
    cantidad    NUMERIC(10,2) NOT NULL,
    motivo      VARCHAR(150)  NOT NULL,
    fechaMerma  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transferencias (
    idTransferencia   SERIAL PRIMARY KEY,
    idInsumo          INTEGER       NOT NULL REFERENCES insumos (idInsumo),
    idAlmacenOrigen   INTEGER       NOT NULL REFERENCES almacenes (idAlmacen),
    idAlmacenDestino  INTEGER       NOT NULL REFERENCES almacenes (idAlmacen),
    idUsuario         INTEGER       NOT NULL REFERENCES usuarios (idUsuario),
    cantidad          NUMERIC(10,2) NOT NULL,
    fecha             TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS logs_actividad (
    idLog      SERIAL PRIMARY KEY,
    idUsuario  INTEGER      NOT NULL REFERENCES usuarios (idUsuario),
    accion     VARCHAR(100) NOT NULL,
    detalles   VARCHAR(255),
    fecha      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS productos (
    idProducto      SERIAL PRIMARY KEY,
    nombre          VARCHAR(100)  NOT NULL,
    descripcion     TEXT,
    precio          NUMERIC(10,2) NOT NULL,
    idCategoria     INTEGER REFERENCES categorias (idCategoria) ON DELETE SET NULL,
    estado          SMALLINT      DEFAULT 1,
    fecha_creacion  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS recetas (
    idReceta        SERIAL PRIMARY KEY,
    idProducto      INTEGER   NOT NULL UNIQUE REFERENCES productos (idProducto) ON DELETE CASCADE,
    instrucciones   TEXT,
    fecha_creacion  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS receta_insumos (
    idRecetaInsumo  SERIAL PRIMARY KEY,
    idReceta        INTEGER       NOT NULL REFERENCES recetas (idReceta) ON DELETE CASCADE,
    idInsumo        INTEGER       NOT NULL REFERENCES insumos (idInsumo) ON DELETE CASCADE,
    cantidad        NUMERIC(10,3) NOT NULL
);

-- Las tablas de ventas no estaban en el volcado; se definen según models/Venta.php
CREATE TABLE IF NOT EXISTS ventas (
    idVenta        SERIAL PRIMARY KEY,
    idAlmacen      INTEGER       NOT NULL REFERENCES almacenes (idAlmacen),
    idUsuario      INTEGER       NOT NULL REFERENCES usuarios (idUsuario),
    fechaVenta     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total          NUMERIC(10,2) NOT NULL DEFAULT 0,
    metodoPago     VARCHAR(20)   NOT NULL CHECK (metodoPago IN ('Efectivo', 'Tarjeta', 'Transferencia')),
    montoRecibido  NUMERIC(10,2)
);

CREATE TABLE IF NOT EXISTS detalle_ventas (
    idDetalleVenta  SERIAL PRIMARY KEY,
    idVenta         INTEGER       NOT NULL REFERENCES ventas (idVenta) ON DELETE CASCADE,
    idProducto      INTEGER       NOT NULL REFERENCES productos (idProducto),
    cantidad        NUMERIC(10,2) NOT NULL,
    precioUnitario  NUMERIC(10,2) NOT NULL,
    subtotal        NUMERIC(10,2) GENERATED ALWAYS AS (cantidad * precioUnitario) STORED
);

-- Índices para las llaves foráneas (MySQL los crea solo; PostgreSQL no)
CREATE INDEX IF NOT EXISTS ix_usuarios_rol          ON usuarios (idRol);
CREATE INDEX IF NOT EXISTS ix_insumos_categoria     ON insumos (idCategoria);
CREATE INDEX IF NOT EXISTS ix_inventarios_insumo    ON inventarios (idInsumo);
CREATE INDEX IF NOT EXISTS ix_compras_proveedor     ON compras (idProveedor);
CREATE INDEX IF NOT EXISTS ix_compras_almacen       ON compras (idAlmacen);
CREATE INDEX IF NOT EXISTS ix_detcompras_compra     ON detalle_compras (idCompra);
CREATE INDEX IF NOT EXISTS ix_detcompras_insumo     ON detalle_compras (idInsumo);
CREATE INDEX IF NOT EXISTS ix_mermas_almacen        ON mermas (idAlmacen);
CREATE INDEX IF NOT EXISTS ix_transferencias_insumo ON transferencias (idInsumo);
CREATE INDEX IF NOT EXISTS ix_logs_usuario          ON logs_actividad (idUsuario);
CREATE INDEX IF NOT EXISTS ix_receta_insumos_receta ON receta_insumos (idReceta);
CREATE INDEX IF NOT EXISTS ix_ventas_almacen        ON ventas (idAlmacen);
CREATE INDEX IF NOT EXISTS ix_detventas_venta       ON detalle_ventas (idVenta);

-- Roles base (el sistema los necesita para iniciar sesión)
INSERT INTO roles (idRol, nombre, descripcion) VALUES
    (1, 'Administrador', 'Acceso total al sistema'),
    (2, 'Bodeguero', 'Gestiona compras, mermas, transferencias e inventario')
ON CONFLICT DO NOTHING;
SELECT setval(pg_get_serial_sequence('roles', 'idrol'), (SELECT MAX(idRol) FROM roles));
