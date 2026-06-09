FROM php:8.4-fpm-bookworm

# C'est CETTE ligne qui manquait dans ton précédent build :
RUN apt-get update && apt-get install -y \
    apache2 \
    libapache2-mod-auth-gssapi \
    krb5-user \
    && a2enmod proxy proxy_http headers auth_gssapi rewrite ssl \
    && apt-get clean

WORKDIR /var/www
COPY . .

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN mkdir -p /etc/ldap && echo "TLS_REQCERT never" >> /etc/ldap/ldap.conf

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]