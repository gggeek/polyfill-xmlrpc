#!/bin/sh

# Has to be run as admin

set -e

echo "Installing base software packages..."

# @todo make updating of preinstalled sw optional, so that we can have faster builds as part of CI

apt-get update

DEBIAN_FRONTEND=noninteractive apt-get install -y \
    sudo unzip wget zip

echo "Done installing base software packages"
