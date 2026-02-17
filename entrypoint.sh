#!/bin/bash
sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf
sed -i "s/*:80/*:${PORT:-80}/g" /etc/apache2/sites-enabled/vhosts.conf
exec apache2-foreground
