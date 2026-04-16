# Usamos imagen de PHP
FROM php:8.4-cli

# Instalamos las dependencias necesarias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql

# Instalamos composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuramos el directorio de trabajo
WORKDIR /app

# Copiamos los archivos de composer primero para aprovechar el cache
COPY composer.json composer.lock ./

# Instalamos las dependencias de Laravel
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiamos el resto del código
COPY . .

# Asegurar que storage y bootstrap/cache existen y tienen permisos
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Exponemos el puerto 8000
EXPOSE 8000

# Comando para ejecutar el servidor de Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]