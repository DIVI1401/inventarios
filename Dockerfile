FROM php:8.2-apache
RUN apt-get update \
 && apt-get install -y --no-install-recommends libpq-dev \
 && docker-php-ext-install pdo pdo_pgsql \
 && rm -rf /var/lib/apt/lists/*
COPY . /var/www/html/
RUN cp /var/www/html/config/Database.example.php /var/www/html/config/Database.php \
 && cp /var/www/html/config/mail_config.example.php /var/www/html/config/mail_config.php \
 && rm -f /var/www/html/database/datos_reales.sql
EXPOSE 80
# Crea las tablas en PostgreSQL (si faltan) y luego arranca Apache
CMD php /var/www/html/database/init.php; apache2-foreground
