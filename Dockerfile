FROM php:8.4-fpm-bookworm

WORKDIR /var/www
COPY . .

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN mkdir -p /etc/ldap && echo "TLS_REQCERT never" >> /etc/ldap/ldap.conf

# Rendre le script exécutable
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]