#!/bin/sh
set -e

# Criar diretórios de cache e storage se não existirem
mkdir -p /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/logs \
         /app/bootstrap/cache

# Otimizações de produção caso configurado
if [ "$APP_ENV" = "production" ]; then
    echo "Caching configurations for production..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Executa migrações caso seja o container principal do app
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

echo "Starting application process: $@"
exec "$@"
