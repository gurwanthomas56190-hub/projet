FROM php:8.4-fpm

# Installation minimale (les extensions PHP sont déjà compilées dans cette image)
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap pdo_sqlite bcmath

WORKDIR /var/www

# On copie tout le code
COPY . .

# On ajuste les permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN echo "TLS_REQCERT never" >> /etc/ldap/ldap.conf

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]