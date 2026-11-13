
FROM php:8.2-apache

# Instalar dependencias necesarias para PHP
RUN apt-get update && apt-get install -y \
    libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Activar módulos de Apache
RUN a2enmod rewrite headers

# Configurar Apache para escuchar en el puerto 10000 de Render
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:10000>/' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY . /var/www/html/

# Permisos para archivos que utiliza el sistema
RUN mkdir -p /var/www/html/uploads /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/uploads /var/www/html/storage \
    && chmod -R 775 /var/www/html/uploads /var/www/html/storage

EXPOSE 10000

