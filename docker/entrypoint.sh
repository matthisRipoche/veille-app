#!/bin/sh
set -e

if [ -z "${API_TOKEN:-}" ]; then
    echo "API_TOKEN manquant : définissez la variable d'environnement API_TOKEN." >&2
    exit 1
fi

# Sans APP_KEY fournie, on en génère une (l'application n'utilise ni session ni donnée chiffrée).
if [ -z "${APP_KEY:-}" ]; then
    export APP_KEY="$(php artisan key:generate --show)"
fi

# Attend que MariaDB accepte les connexions, puis applique les migrations.
tries=0
until php artisan migrate --force; do
    tries=$((tries + 1))
    if [ "$tries" -ge 30 ]; then
        echo "Base de données injoignable après $tries tentatives." >&2
        exit 1
    fi
    echo "Base de données pas encore prête, nouvelle tentative..." >&2
    sleep 2
done

php artisan config:cache
php artisan view:cache

exec docker-php-entrypoint "$@"
