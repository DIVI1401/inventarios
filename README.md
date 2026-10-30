# AetherStock

Sistema web de inventario para 3 restaurantes: control de insumos, compras, mermas, transferencias entre sucursales, productos con recetas y ventas. Proyecto de Ingeniería de Software (4to semestre).

## Tecnologías
PHP (MVC), MySQL/MariaDB, XAMPP, PHPMailer.

## Instalación
1. Clona el repositorio dentro de `C:\xampp\htdocs`.
2. Crea la base de datos `aetherstock` e importa `database/schema.sql`.
3. Copia `config/Database.example.php` como `config/Database.php` y ajusta tus datos.
4. Copia `config/mail_config.example.php` como `config/mail_config.php` y pon tus credenciales SMTP.
5. Abre `http://localhost/aetherstock/index.php`.

## Despliegue en Render (PostgreSQL)
1. En Render crea un **PostgreSQL** (New → Postgres) en la misma región que el Web Service.
2. En el Web Service → Environment agrega:
   - `DATABASE_URL` = la *Internal Database URL* del Postgres
   - `ADMIN_EMAIL` y `ADMIN_PASSWORD` = usuario administrador inicial (solo se crea si no hay usuarios)
3. Al arrancar, el contenedor ejecuta `database/init.php`, que crea las tablas de `database/schema.sql` si no existen.

## Equipo
Oscar Álvarez, Fernando Arteaga, Juan Simanca, Hernán Oliveros