#!/bin/sh
# Création du .env s'il n'existe pas
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Création de la base SQLite si nécessaire
touch database/database.sqlite

# Migration de la base de données
php artisan migrate --force

exec "$@"