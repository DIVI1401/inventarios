FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY . /var/www/html/
RUN cp /var/www/html/config/Database.example.php /var/www/html/config/Database.php \
 && cp /var/www/html/config/mail_config.example.php /var/www/html/config/mail_config.php
EXPOSE 80