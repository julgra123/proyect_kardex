
FROM php:8.2-apache

# Extensiones necesarias para el proyecto
RUN docker-php-ext-install pdo_mysql mbstring

# Módulos de Apache
RUN a2enmod rewrite headers

# Render utiliza por defecto el puerto 10000
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:10000>/' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY . /var/www/html/

# Permisos para el almacenamiento interno
RUN mkdir -p /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 750 /var/www/html/storage

EXPOSE 10000
