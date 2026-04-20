FROM php:8.3-apache

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN a2enmod rewrite headers

COPY apache/vhosts.conf /etc/apache2/sites-enabled/vhosts.conf

COPY sites/hokata.de/  /var/www/hokata.de/
COPY sites/dlyx.io/    /var/www/dlyx.io/
COPY sites/playground.dlyx.io/    /var/www/playground.dlyx.io/
COPY sites/hdengine.io/ /var/www/hdengine.io/
COPY sites/holyx.io/ /var/www/holyx.io/

RUN chown -R www-data:www-data /var/www/

EXPOSE 80
