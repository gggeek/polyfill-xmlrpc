#!/bin/sh

# Has to be run as admin

# @todo make it optional to install xdebug. It is fe. missing in sury's ppa for Xenial
# @todo make it optional to install fpm
# @todo make it optional to disable xdebug ?

set -e

echo "Installing PHP version '${1}'..."

SCRIPT_DIR="$(dirname -- "$(readlink -f "$0")")"

configure_php_ini() {
    # note: these settings are not required for cli config
    echo "cgi.fix_pathinfo = 1" >> "${1}"
    echo "always_populate_raw_post_data = -1" >> "${1}"

    # we disable xdebug for speed for both cli and web mode
    # @todo make this optional
    if which phpdismod >/dev/null 2>/dev/null; then
        phpdismod xdebug
    elif [ -f /usr/local/php/$PHP_VERSION/etc/conf.d/20-xdebug.ini ]; then
        mv /usr/local/php/$PHP_VERSION/etc/conf.d/20-xdebug.ini /usr/local/php/$PHP_VERSION/etc/conf.d/20-xdebug.ini.bak
    fi
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

# @todo use native packages if requested for a specific version and that is the same as available in the os repos

if [ "${PHP_VERSION}" = default ]; then
    echo "Using native PHP packages..."

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
    # on GHA runners ubuntu version, php 7.4 and 8.0 seem to be preinstalled. Remove them if found
    for PHP_CURRENT in $(dpkg -l | grep -E 'php.+-common' | awk '{print $2}'); do
        if [ "${PHP_CURRENT}" != "php${PHP_VERSION}-common" ]; then
            apt-get purge -y "${PHP_CURRENT}"
        fi
    done

    # @todo use php from shivammathur/php5-ubuntu for php versions 5.4, 5.5 (see the phpxmlrpc repo)

    echo "Using PHP packages from ondrej/php..."

    DEBIAN_FRONTEND=noninteractive apt-get install -y language-pack-en-base software-properties-common
    LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php
    apt-get update

    XMLRPC_PACKAGE=$(apt-cache search "php${PHP_VERSION}-xmlrpc")
    if [ -n "${XMLRPC_PACKAGE}" ]; then
        XMLRPC_PACKAGE="php${PHP_VERSION}-xmlrpc"
    fi

        PHP_PACKAGES="php${PHP_VERSION} \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-dom \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-mbstring \
        $XMLRPC_PACKAGE"
        # @todo remove this IF once xdebug is compatible and available
        if [ "${PHP_VERSION}" != 8.4 ]; then
            PHP_PACKAGES="${PHP_PACKAGES} php${PHP_VERSION}-xdebug"
        fi
        DEBIAN_FRONTEND=noninteractive apt-get install -y ${PHP_PACKAGES}

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

service "php${PHPVER}-fpm" stop || true

if [ -d /etc/php/${PHPVER}/fpm ]; then
    configure_php_ini /etc/php/${PHPVER}/fpm/php.ini
elif [ -f /usr/local/php/${PHPVER}/etc/php.ini ]; then
    configure_php_ini /usr/local/php/${PHPVER}/etc/php.ini
fi

# use a nice name for the php-fpm service, so that it does not depend on php version running. Try to make that work
# both for docker and VMs
if [ -f "/etc/init.d/php${PHPVER}-fpm" ]; then
    ln -s "/etc/init.d/php${PHPVER}-fpm" /etc/init.d/php-fpm
fi
if [ -f "/lib/systemd/system/php${PHPVER}-fpm.service" ]; then
    ln -s "/lib/systemd/system/php${PHPVER}-fpm.service" /lib/systemd/system/php-fpm.service
    if [ ! -f /.dockerenv ]; then
        systemctl daemon-reload
    fi
fi

# @todo shall we configure php-fpm?

service php-fpm start

# reconfigure apache (if installed). Sadly, php will switch on mod-php and mpm_prefork at install time...
if [ -n "$(dpkg --list | grep apache)" ]; then
    echo "Reconfiguring Apache..."
    if [ -n "$(ls /etc/apache2/mods-enabled/php* 2>/dev/null)" ]; then
        rm /etc/apache2/mods-enabled/php*
    fi
    a2dismod mpm_prefork
    a2enmod mpm_event
    a2enconf php${PHPVER}-fpm
    service apache2 restart
fi

echo "Done installing PHP"
