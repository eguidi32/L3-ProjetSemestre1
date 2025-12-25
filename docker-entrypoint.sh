#!/bin/bash
set -e

# Configurer le port Apache (Render fournit la variable PORT)
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
    sed -i "s/:80/:$PORT/" /etc/apache2/sites-available/000-default.conf
fi

# Vider le cache Symfony
php bin/console cache:clear --env=prod --no-debug || true
php bin/console cache:warmup --env=prod --no-debug || true

# S'assurer que les permissions sont correctes
chown -R www-data:www-data var

# Démarrer Apache
exec "$@"
