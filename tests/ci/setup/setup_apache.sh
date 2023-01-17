#!/bin/sh

# Install and configure apache2
# Has to be run as admin
# @todo make this work across all apache versions (precise to focal)

set -e

SCRIPT_DIR="$(dirname -- "$(readlink -f "$0")")"

DEBIAN_FRONTEND=noninteractive apt-get install -y apache2

# set up Apache for php-fpm

a2enmod rewrite proxy_fcgi setenvif ssl

# configure apache virtual hosts

cp -f "$SCRIPT_DIR/../config/apache_vhost" /etc/apache2/sites-available/000-default.conf

echo "export TESTS_ROOT_DIR=/var/www/html" >> /etc/apache2/envvars
echo "export HTTPSERVER=localhost" >> /etc/apache2/envvars

service apache2 restart
