#!/bin/sh
set -e
cd /var/www/html

if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing Composer dependencies..."
    for i in 1 2 3; do
        composer update --no-interaction --prefer-dist --no-security-blocking && break
        echo "Retry $i..."; sleep 3
    done
fi

[ ! -f ".env" ] && cp .env.example .env

echo "Waiting for MySQL..."
RETRIES=0
until php -r "try{new PDO('mysql:host='.getenv('DB_HOST').';dbname='.getenv('DB_DATABASE'),getenv('DB_USER'),getenv('DB_PASSWORD'));echo 'ok';}catch(Exception \$e){exit(1);}" 2>/dev/null; do
    RETRIES=$((RETRIES+1))
    [ $RETRIES -ge 40 ] && echo "MySQL not ready, continuing anyway..." && break
    sleep 2
done

echo "Generating JWT keys if needed..."
mkdir -p config/jwt
if [ ! -f "config/jwt/private.pem" ]; then
    openssl genrsa -passout pass:product_jwt_passphrase -out config/jwt/private.pem 4096 2>/dev/null
    openssl rsa -passin pass:product_jwt_passphrase -in config/jwt/private.pem -pubout -out config/jwt/public.pem 2>/dev/null
fi

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction 2>/dev/null || true

chmod -R 775 var/ 2>/dev/null || true

# If command is provided (worker), run it; otherwise php-fpm
if [ "$#" -gt 0 ]; then
    exec "$@"
fi

exec php-fpm
