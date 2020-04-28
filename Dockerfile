FROM php:7.2-apache

WORKDIR /var/www/unifi

COPY . .

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && a2enmod rewrite \
 && find apache2/ -type f -print0 | xargs -0 -I {} mv {} /etc/{} \
 && rm -rf apache2 \
 && chmod +x periodic.sh \
 && mv periodic.sh /usr/local/bin
