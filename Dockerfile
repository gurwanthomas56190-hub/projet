# On utilise une image PHP qui a déjà les outils de base
FROM php:8.4-fpm-bookworm

# Au lieu d'apt-get, on utilise l'outil interne de PHP pour installer LDAP
# Il est conçu pour ne pas dépendre du réseau si les bibliothèques sont présentes
RUN apt-get update && apt-get install -y libldap2-dev && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap pdo_sqlite bcmath

# Le reste ne change pas
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
COPY . .
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN echo "TLS_REQCERT never" >> /etc/ldap/ldap.conf

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]