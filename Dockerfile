FROM php:8.4-fpm

# Installation des dépendances système (incluant Node.js pour Vite)
RUN apt-get update && apt-get install -y \
    git curl zip unzip libldap2-dev libsqlite3-dev \
    && curl -sL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installation des extensions PHP (Une seule fois !)
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap pdo_sqlite bcmath

# Copie de Composer (Une seule fois !)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Installation et build automatique
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Paramétrage des droits
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# ---> DÉSACTIVATION DE LA VÉRIFICATION SSL STRICTE <---
RUN echo "TLS_REQCERT never" >> /etc/ldap/ldap.conf

# Script d'entrée pour finaliser la config au démarrage
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]