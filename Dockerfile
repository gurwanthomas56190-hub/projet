# Utiliser une image PHP qui inclut déjà certaines dépendances
FROM php:8.4-fpm

# 1. On saute l'installation via apt-get pour éviter les erreurs réseau.
# Si tu as besoin d'outils, installe-les directement sur la machine hôte (ciel@ciel)
# et copie-les si nécessaire, ou utilise une image plus complète.

# 2. Installation des extensions PHP (le minimum vital)
RUN apt-get update && apt-get install -y libldap2-dev libsqlite3-dev \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap pdo_sqlite bcmath \
    && apt-get clean

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# On ne fait PAS de 'npm install' ici, fais-le sur la machine hôte (ciel@ciel)
# et copie le dossier 'public/build' déjà généré.
# RUN npm install && npm run build 

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN echo "TLS_REQCERT never" >> /etc/ldap/ldap.conf

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]