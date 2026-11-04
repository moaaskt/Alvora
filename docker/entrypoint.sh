#!/bin/sh
set -e

# Criar diretórios de cache e storage se não existirem
mkdir -p /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/logs \
         /app/bootstrap/cache

# Garante link simbólico do storage público
php artisan storage:link || true

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
    MAX_TRIES=5
    COUNT=0
    until php artisan migrate --force || [ $COUNT -ge $MAX_TRIES ]; do
        COUNT=$((COUNT + 1))
        echo "Database connection or migration failed. Retrying ($COUNT/$MAX_TRIES) in 3 seconds..."
        sleep 3
    done
    if [ $COUNT -ge $MAX_TRIES ]; then
        echo "Failed to run migrations after $MAX_TRIES attempts."
        exit 1
    fi
fi

# Se a porta foi customizada via PORT (ex: no Render) e o comando for artisan serve
if [ "$1" = "php" ] && [ "$2" = "artisan" ] && [ "$3" = "serve" ] && [ -n "$PORT" ]; then
    echo "Starting application process on port $PORT..."
    exec php artisan serve --host=0.0.0.0 --port="$PORT"
fi

echo "Starting application process: $@"
exec "$@"
