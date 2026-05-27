FROM php:8.4-fpm-bookworm

# 1. On installe la librairie LDAP de Debian et on l'active dans PHP
RUN apt-get update && apt-get install -y libldap2-dev \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap

WORKDIR /var/www
COPY . .

# 2. Permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# 3. Configuration LDAP pour ignorer les certificats SSL auto-signés
RUN mkdir -p /etc/ldap && echo "TLS_REQCERT never" >> /etc/ldap/ldap.conf

# 4. Script de démarrage
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]