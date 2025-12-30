FROM php:8.3-fpm

# Installa le dipendenze di sistema
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    && docker-php-ext-install pdo pdo_mysql zip intl opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installa Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurazione PHP per produzione
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'realpath_cache_size=4096K'; \
    echo 'realpath_cache_ttl=600'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Imposta la directory di lavoro
WORKDIR /var/www/html

# Copia i file composer per sfruttare la cache Docker
COPY composer.json composer.lock symfony.lock ./

# Installa le dipendenze
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copia il resto del codice
COPY . .

# Permessi per le directory di Symfony
RUN chown -R www-data:www-data /var/www/html/var

EXPOSE 9000

CMD ["php-fpm"]
