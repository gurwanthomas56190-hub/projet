# Utilisation de l'image PHP officielle avec FPM
FROM php:8.2-fpm

# Installation des dépendances système et des extensions PHP
RUN apt-get update && apt-get install -y \
    libldap2-dev \
    libsqlite3-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install ldap pdo_sqlite

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définition du répertoire de travail
WORKDIR /var/www

# Copie des fichiers du projet
COPY . .

# Installation des dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Ajustement des permissions pour Laravel
RUN chown -妥 R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Exposition du port (interne au conteneur)
EXPOSE 9000

CMD ["php-fpm"]