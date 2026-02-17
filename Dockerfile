FROM php:8.3-apache

COPY sites/hdengine.io/ /var/www/html/

EXPOSE 80
