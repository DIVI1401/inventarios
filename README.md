# AetherStock

Sistema web de inventarios para una cadena de 3 restaurantes. Controla los insumos de cada sucursal desde que se compran hasta que se consumen en una venta, se pierden como merma o se transfieren a otra sede.

Proyecto de Ingeniería de Software (4.º semestre).

**Equipo:** Oscar Álvarez, Fernando Arteaga, Juan Simanca, Hernán Oliveros.

---

## Funcionalidades

| Módulo | Qué hace |
|---|---|
| Usuarios y roles | Inicio de sesión con contraseñas cifradas (`password_hash`), roles Administrador y Bodeguero, perfil propio, desactivar en lugar de borrar. |
| Almacenes | Las sucursales donde se guarda el stock. |
| Categorías e insumos | Catálogo de insumos con unidad de medida, precio de referencia y stock mínimo por sucursal. |
| Proveedores | Datos de contacto, historial y comparación de precios entre proveedores. |
| Compras | Ingreso de mercancía por sucursal. El precio se autocompleta con el último pagado a ese proveedor y cada lote registra su **fecha de vencimiento**. |
| Mermas | Salidas por daño o pérdida, con motivo. |
| Transferencias | Movimiento de insumos entre sucursales. |
| Productos y recetas | Cada producto tiene una receta (insumos y cantidades). El **precio de venta se calcula solo**: `costo de la receta ÷ (1 − margen)`, con un margen por defecto del 70 % (regla de *food cost* del 30 %). |
| Ventas | Toma los precios de la base de datos, descuenta los insumos según la receta y bloquea la venta si falta stock. Genera comprobante. |
| Alertas | Stock bajo, lotes próximos a vencer, sugerencias de compra y avisos por correo. |
| Reportes | Ventas, compras por proveedor, mermas, precios, auditoría y exportación del inventario a Excel. |

Todo lo que modifica el stock (compras, mermas, transferencias y ventas) se ejecuta dentro de una transacción.

### Permisos por rol

| | Administrador | Bodeguero |
|---|:---:|:---:|
| Usuarios, almacenes, categorías, insumos, proveedores | ✅ | — |
| Productos y recetas | ✅ | — |
| Compras, ventas, mermas, transferencias | ✅ | ✅ |
| Inventario, alertas, stock de productos, exportar inventario | ✅ | ✅ |
| Reportes de mermas y de precios | ✅ | ✅ |
| Reportes de ventas, compras y auditoría | ✅ | — |

---

## Tecnologías

- **Backend:** PHP 8.2 sin frameworks, con arquitectura MVC propia (`index.php` funciona como router).
- **Base de datos:** PostgreSQL.
- **Frontend:** HTML, CSS propio y Bootstrap 5 (CDN).
- **Correo:** API de [Brevo](https://www.brevo.com) en producción y PHPMailer (SMTP) en local.
- **Despliegue:** Docker (`php:8.2-apache`) en [Render](https://render.com).

## Estructura

```
config/        Conexión a la BD y correo (*.example.php se versionan; los reales no)
controllers/   Un controlador por módulo + middleware.php (login y roles)
models/        Acceso a datos (PDO) y EmailService
views/         Una carpeta por módulo + layouts/header.php y footer.php
database/      schema.sql (estructura) e init.php (crea tablas y aplica migraciones)
libs/          PHPMailer
public/css/    Estilos
index.php      Router: index.php?action=modulo.accion
Dockerfile     Imagen para Render
```

---

## Instalación local (XAMPP + PostgreSQL)

1. Clona el repositorio dentro de `C:\xampp\htdocs`:
   ```bash
   git clone https://github.com/DIVI1401/inventarios.git aetherstock
   ```
2. Instala [PostgreSQL](https://www.postgresql.org/download/windows/) y crea la base:
   ```bash
   psql -U postgres -c "CREATE DATABASE aetherstock"
   psql -U postgres -d aetherstock -f database/schema.sql
   ```
3. Activa PostgreSQL en PHP: en `C:\xampp\php\php.ini` quita el `;` de `extension=pdo_pgsql` y de `extension=pgsql`, y reinicia Apache.
4. Copia `config/Database.example.php` como `config/Database.php` y pon tu contraseña de PostgreSQL en `DB_PASSWORD`.
5. Copia `config/mail_config.example.php` como `config/mail_config.php` (ver [Correo](#correo)).
6. Crea el primer administrador:
   ```bash
   set ADMIN_EMAIL=admin@correo.com
   set ADMIN_PASSWORD=unaClaveSegura
   C:\xampp\php\php.exe database\init.php
   ```
7. Abre `http://localhost/aetherstock/index.php`.

## Despliegue en Render

1. Crea un **PostgreSQL** (New → Postgres) en la misma región que el Web Service.
2. Crea un **Web Service** desde este repositorio con runtime **Docker**.
3. En el Web Service → *Environment* agrega:

   | Variable | Valor |
   |---|---|
   | `DATABASE_URL` | La *Internal Database URL* del Postgres |
   | `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Administrador inicial (solo se crea si no hay usuarios) |
   | `BREVO_API_KEY` | Clave de API de Brevo (`xkeysib-...`) |
   | `MAIL_FROM` | Correo remitente verificado en Brevo |
   | `MAIL_TO` | *(Opcional)* Destinatarios separados por coma. Si se omite, se envía a los administradores activos |

4. En cada arranque el contenedor ejecuta `database/init.php`, que:
   - crea las tablas de `schema.sql` si la base está vacía;
   - aplica las **migraciones** pendientes, por ejemplo columnas nuevas. Son idempotentes, así que no dañan datos existentes.

En producción los errores de PHP no se muestran en pantalla: van al log de Render.

## Correo

Render bloquea los puertos SMTP en el plan gratuito, por eso en producción las alertas se envían por la **API HTTPS de Brevo** (gratis, 300 correos/día):

1. Crea una cuenta en brevo.com.
2. En *Senders, domains & IPs → Senders*, agrega y verifica tu correo como remitente.
3. En *SMTP & API → API Keys*, genera una clave.
4. Configura `BREVO_API_KEY` y `MAIL_FROM`.

En local también puedes usar SMTP (por ejemplo Gmail con una *contraseña de aplicación*) llenando `MAIL_HOST`, `MAIL_USER`, `MAIL_PASSWORD` y `MAIL_PORT` en `config/mail_config.php`. Si hay `BREVO_API_KEY`, se usa Brevo.

Se envían correos automáticamente cuando una merma, transferencia o venta deja un insumo en su stock mínimo o por debajo de él, y manualmente desde *Alertas y Sugerencias*.
