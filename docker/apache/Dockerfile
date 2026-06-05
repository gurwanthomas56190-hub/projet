FROM debian:bookworm-slim

RUN apt-get update && apt-get install -y \
    apache2 \
    libapache2-mod-auth-gssapi \
    krb5-user \
    && a2enmod proxy proxy_http headers auth_gssapi rewrite \
    && apt-get clean

# Rediriger les logs pour les voir avec "docker compose logs"
RUN ln -sf /dev/stdout /var/log/apache2/access.log \
    && ln -sf /dev/stderr /var/log/apache2/error.log

COPY sso.conf /etc/apache2/sites-available/000-default.conf

CMD ["apache2ctl", "-D", "FOREGROUND"]