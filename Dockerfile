FROM php:8.3-apache

ENV PORT=80

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN a2enmod rewrite headers

RUN a2dissite 000-default

COPY apache/vhosts.conf /etc/apache2/sites-enabled/vhosts.conf

COPY sites/hokata.de/  /var/www/hokata.de/
COPY sites/dlyx.io/    /var/www/dlyx.io/
COPY sites/hdengine.io/ /var/www/hdengine.io/

RUN chown -R www-data:www-data /var/www/

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE ${PORT}

ENTRYPOINT ["/entrypoint.sh"]
