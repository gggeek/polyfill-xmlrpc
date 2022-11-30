#!/bin/sh

# Has to be run as admin

# To be kept in sync with setup_php_travis.sh

# @todo make it optional to disable xdebug ?

set -e

configure_php_ini() {
    # note: these settings are not required for cli config
    echo "cgi.fix_pathinfo = 1" >> "${1}"
    echo "always_populate_raw_post_data = -1" >> "${1}"

    # we disable xdebug for speed for both cli and web mode
    phpdismod xdebug
}

# install php
PHP_VERSION="$1"
# `lsb-release` is not necessarily onboard. We parse /etc/os-release instead
DEBIAN_VERSION=$(cat /etc/os-release | grep 'VERSION_CODENAME=' | sed 's/VERSION_CODENAME=//')
if [ -z "${DEBIAN_VERSION}" ]; then
    # Example strings:
    # VERSION="14.04.6 LTS, Trusty Tahr"
    # VERSION="8 (jessie)"
    DEBIAN_VERSION=$(cat /etc/os-release | grep 'VERSION=' | grep 'VERSION=' | sed 's/VERSION=//' | sed 's/"[0-9.]\+ *(\?//' | sed 's/)\?"//' | tr '[:upper:]' '[:lower:]' | sed 's/lts, *//' | sed 's/ \+tahr//')
fi

if [ "${PHP_VERSION}" = default ]; then
    if [ "${DEBIAN_VERSION}" = jessie -o "${DEBIAN_VERSION}" = precise -o "${DEBIAN_VERSION}" = trusty ]; then
        PHPSUFFIX=5
    else
        PHPSUFFIX=
    fi

    XMLRPC_PACKAGE=$(apt-cache search "php${PHPSUFFIX}-xmlrpc")
    if [ -n "${XMLRPC_PACKAGE}" ]; then
        XMLRPC_PACKAGE="php${PHPSUFFIX}-xmlrpc"
    fi

    # @todo check for mbstring presence in php5 (jessie) packages
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        php${PHPSUFFIX} \
        php${PHPSUFFIX}-cli \
        php${PHPSUFFIX}-dom \
        php${PHPSUFFIX}-curl \
        php${PHPSUFFIX}-fpm \
        php${PHPSUFFIX}-mbstring \
        php${PHPSUFFIX}-xdebug \
        $XMLRPC_PACKAGE

    if [ -z "${XMLRPC_PACKAGE}" ]; then
        # Most likely php8 land
        # @todo build it by hand...
        :
    fi
else
    DEBIAN_FRONTEND=noninteractive apt-get install -y language-pack-en-base software-properties-common
    LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php
    apt-get update

    XMLRPC_PACKAGE=$(apt-cache search "php${PHP_VERSION}-xmlrpc")
    if [ -n "${XMLRPC_PACKAGE}" ]; then
        XMLRPC_PACKAGE="php${PHP_VERSION}-xmlrpc"
    fi

    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        php${PHP_VERSION} \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-dom \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-xdebug \
        $XMLRPC_PACKAGE

    update-alternatives --set php /usr/bin/php${PHP_VERSION}

    if [ -z "${XMLRPC_PACKAGE}" ]; then
        # Build and enable the xmlrpc extension by hand
        ## 1. Install dev tools
        DEBIAN_FRONTEND=noninteractive apt-get install -y php${PHP_VERSION}-dev libexpat1-dev libxml2-dev git
        # 2. optional - install packages libxmlrpc-epi0 and libxmlrpc-epi-dev (they are not older than upstream version from sourceforge anyway)
        # DEBIAN_FRONTEND=noninteractive apt-get install -y libxmlrpc-epi0 libxmlrpc-epi-dev
        # 3. build extension from PECL (it is not older than upsteram version on sourceforge anyway)
        # pecl install xmlrpc-devel <= KO
        git clone https://git.php.net/repository/pecl/networking/xmlrpc.git
        export CPPFLAGS=-I/usr/include/libxml2/
        cd xmlrpc && phpize && ./configure --with-expat && make && make install
        echo "extension=xmlrpc.so" > /etc/php/${PHP_VERSION}/mods-available/xmlrpc.ini
        ln -s /etc/php/${PHP_VERSION}/mods-available/xmlrpc.ini /etc/php/${PHP_VERSION}/cli/conf.d/20-xmlrpc.ini
        ln -s /etc/php/${PHP_VERSION}/mods-available/xmlrpc.ini /etc/php/${PHP_VERSION}/fpm/conf.d/20-xmlrpc.ini
    fi
fi

PHPVER=$(php -r 'echo implode(".",array_slice(explode(".",PHP_VERSION),0,2));' 2>/dev/null)

configure_php_ini /etc/php/${PHPVER}/fpm/php.ini

# use a nice name for the php-fpm service, so that it does not depend on php version running
service "php${PHPVER}-fpm" stop
ln -s "/etc/init.d/php${PHPVER}-fpm" /etc/init.d/php-fpm

# @todo shall we configure php-fpm?

service php-fpm start

# configure apache
a2enconf php${PHPVER}-fpm
service apache2 restart
