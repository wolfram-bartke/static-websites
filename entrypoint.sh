#!/bin/bash
echo "=== DEBUG START ==="
echo "PORT env: '${PORT}'"
echo "PORT used: '${PORT:-80}'"
echo "All env vars:"
env | sort
echo ""
echo "=== ports.conf BEFORE ==="
cat /etc/apache2/ports.conf
echo ""
echo "=== vhosts.conf BEFORE ==="
cat /etc/apache2/sites-enabled/vhosts.conf
echo ""

sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf
sed -i "s/\*:80/\*:${PORT:-80}/g" /etc/apache2/sites-enabled/vhosts.conf

echo "=== ports.conf AFTER ==="
cat /etc/apache2/ports.conf
echo ""
echo "=== vhosts.conf AFTER ==="
cat /etc/apache2/sites-enabled/vhosts.conf
echo ""
echo "=== Apache config test ==="
apache2ctl configtest 2>&1
echo ""
echo "=== Starting Apache ==="
exec apache2-foreground
