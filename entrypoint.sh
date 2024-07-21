#!/bin/bash
set -e

# Function to start Horizon with retries
start_horizon() {
    local retries=10
    local count=0
    local delay=5

    until php artisan horizon; do
        count=$((count + 1))
        if [ $count -ge $retries ]; then
            echo "Horizon failed to start after $retries attempts."
            exit 1
        fi
        echo "Horizon failed to start. Retrying in $delay seconds... ($count/$retries)"
        sleep $delay
    done
    echo "Horizon started successfully."
}

echo "Start entrypoint file"

echo "APACHE_REMOTE_IP_HEADER: ${APACHE_REMOTE_IP_HEADER}"
echo "APACHE_REMOTE_IP_TRUSTED_PROXY: ${APACHE_REMOTE_IP_TRUSTED_PROXY}"
echo "APACHE_REMOTE_IP_INTERNAL_PROXY: ${APACHE_REMOTE_IP_INTERNAL_PROXY}"

echo "Setup TZ"
php -r "date_default_timezone_set('${TZ}');"
php -r "echo date_default_timezone_get();"

if [ -f /vault/secrets/secrets.env ]; then
    touch .env && cp -rf /vault/secrets/secrets.env /var/www/html/.env
fi
if [ -f /vault/secrets/test-secrets.env ]; then
    touch .env && cp -rf /vault/secrets/test-secrets.env /var/www/html/.env
fi
echo "ENV_ARG: ${ENV_ARG}"

# Add cron job for Laravel schedule:run
echo "Install Cron"
echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" | crontab -

echo "Install composer"
composer dump-autoload

chmod 766 /var/www/html/probe-check.sh

echo "Permissions setup for NPM:"
chmod -R a+w node_modules

echo "Starting apache in the background:"
/usr/sbin/apache2ctl start

echo "Start Horizon"
php artisan cache:clear

# Start Horizon with retries in the background
start_horizon &

# Keep the script running to prevent the container from exiting
tail -f /dev/null
