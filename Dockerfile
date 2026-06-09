FROM php:8.4-fpm-bookworm

# On installe UNIQUEMENT les dépendances nécessaires à PHP (et notamment libldap2-dev pour Active Directory)
RUN apt-get update && apt-get install -y \
    libldap2-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure ldap \
    && docker-php-ext-install ldap zip pdo pdo_mysql \
    && apt-get clean

WORKDIR /var/www
COPY . .

# Droits pour Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Configuration pour accepter les certificats LDAP locaux (très important pour l'AD Silvadec)
RUN mkdir -p /etc/ldap && echo "TLS_REQCERT never" >> /etc/ldap/ldap.conf

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]