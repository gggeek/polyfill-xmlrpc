#!/bin/sh

# Has to be run as admin

set -e

echo "Installing base software packages..."

apt-get update

DEBIAN_FRONTEND=noninteractive apt-get install -y \
    sudo unzip wget zip

echo "Done installing base software packages"
