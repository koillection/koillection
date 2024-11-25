#!/bin/sh

set -e

echo "**** Inject .env values ****"
rm -rf /var/www/koillection/.env.local
touch /var/www/koillection/.env.local

echo "APP_ENV=${APP_ENV:-prod}" >> "/var/www/koillection/.env.local"
echo "APP_DEBUG=${APP_DEBUG:-0}" >> "/var/www/koillection/.env.local"
echo "APP_SECRET=${APP_SECRET:-$(openssl rand -base64 21)}" >> "/var/www/koillection/.env.local"

echo "JWT_SECRET_KEY=${JWT_SECRET_KEY:-%kernel.project_dir%/config/jwt/private.pem}" >> "/var/www/koillection/.env.local"
echo "JWT_PUBLIC_KEY=${JWT_PUBLIC_KEY:-%kernel.project_dir%/config/jwt/public.pem}" >> "/var/www/koillection/.env.local"
echo "JWT_PASSPHRASE=${JWT_PASSPHRASE:-$(openssl rand -base64 21)}" >> "/var/www/koillection/.env.local"

echo "JWT_SECRET_KEY=${JWT_SECRET_KEY:-%kernel.project_dir%/config/jwt/private.pem}" >> "/var/www/koillection/.env.local"
echo "JWT_PUBLIC_KEY=${JWT_PUBLIC_KEY:-%kernel.project_dir%/config/jwt/public.pem}" >> "/var/www/koillection/.env.local"
echo "JWT_PASSPHRASE=${JWT_PASSPHRASE:-$(openssl rand -base64 21)}" >> "/var/www/koillection/.env.local"

echo "DB_DRIVER=${DB_DRIVER:-}" >> "/var/www/koillection/.env.local"
echo "DB_NAME=${DB_NAME:-}" >> "/var/www/koillection/.env.local"
echo "DB_HOST=${DB_HOST:-}" >> "/var/www/koillection/.env.local"
echo "DB_PORT=${DB_PORT:-}" >> "/var/www/koillection/.env.local"
echo "DB_USER=${DB_USER:-}" >> "/var/www/koillection/.env.local"
echo "DB_PASSWORD=${DB_PASSWORD:-}" >> "/var/www/koillection/.env.local"
echo "DB_VERSION=${DB_VERSION:-}" >> "/var/www/koillection/.env.local"

echo "CORS_ALLOW_ORIGIN=${CORS_ALLOW_ORIGIN:-'^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'}" >> "/var/www/koillection/.env.local"

echo "session.cookie_secure=${HTTPS_ENABLED}" >> /etc/php/8.4/fpm/conf.d/php.ini
echo "date.timezone=${PHP_TZ}" >> /etc/php/8.4/fpm/conf.d/php.ini
echo "memory_limit=${PHP_MEMORY_LIMIT:-'512M'}" >> /etc/php/8.4/fpm/conf.d/php.ini

echo "upload_max_filesize=${UPLOAD_MAX_FILESIZE:-'100M'}" >> /etc/php/8.4/fpm/conf.d/php.ini
echo "post_max_size=${UPLOAD_MAX_FILESIZE:-'100M'}" >> /etc/php/8.4/fpm/conf.d/php.ini
sed -i "s/client_max_body_size 100M;/client_max_body_size ${UPLOAD_MAX_FILESIZE:-'100M'};/g" /etc/nginx/nginx.conf

echo "**** Install dependencies ****"
cd /var/www/koillection && \
composer install
composer dump-env ${APP_ENV:-dev}

echo "**** Migrate the database ****"
php bin/console doctrine:migration:migrate --no-interaction --allow-no-migration --env=prod

echo "**** Create API keys ****"
cd /var/www/koillection && \
php bin/console lexik:jwt:generate-keypair --skip-if-exists --env=prod

echo "**** Create nginx log files ****"
mkdir -p /logs/nginx
chown -R www-data:www-data /logs/nginx

echo "**** Setup complete, starting the server. ****"
php-fpm8.4
chown -R www-data:www-data /var/www/koillection/public/uploads
exec $@