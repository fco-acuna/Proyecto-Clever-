FROM php:8.3-apache

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
        libzip-dev \
        libonig-dev \
        libxml2-dev \
        default-mysql-client \
        && docker-php-ext-install pdo pdo_mysql mysqli

# Copiar código al contenedor
COPY src/ /var/www/html/

EXPOSE 80
