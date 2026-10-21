# =========================================================
# Stage 1: Build Assets com Node.js e Vite
# =========================================================
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci --prefer-offline --no-audit
COPY . .
RUN npm run build

# =========================================================
# Stage 2: Imagem Base de Produção com FrankenPHP e Octane
# =========================================================
FROM dunglas/frankenphp:1-php8.3-alpine AS production

WORKDIR /app

# Instala extensões PHP de alta performance para Laravel e Horizon
RUN install-php-extensions \
    pdo_mysql \
    pcntl \
    posix \
    redis \
    bcmath \
    zip \
    intl \
    gd \
    opcache

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configuração de PHP para produção
RUN cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini && \
    echo "opcache.enable_cli=1" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=256" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=20000" >> $PHP_INI_DIR/conf.d/opcache.ini && \
    echo "memory_limit=512M" >> $PHP_INI_DIR/conf.d/memory.ini

# Copia código-fonte da aplicação
COPY . .

# Copia assets compilados do stage frontend
COPY --from=frontend /app/public/build ./public/build

# Instala dependências PHP sem dev
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Configura script de entrypoint e permissões
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh && \
    chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    chmod -R 775 /app/storage /app/bootstrap/cache

# Porta HTTP do Octane / FrankenPHP
EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]
