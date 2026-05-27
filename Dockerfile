FROM php:8.4-fpm

# On retire l'installation via apt-get qui bloque
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap pdo_sqlite bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# On suppose que tu as déjà les dépendances node_modules localement
# Si tu ne les as pas, il faudra les copier depuis ta machine hôte
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN echo "TLS_REQCERT never" >> /etc/ldap/ldap.conf

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]