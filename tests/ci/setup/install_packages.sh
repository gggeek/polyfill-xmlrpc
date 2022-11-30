#!/bin/sh

# Has to be run as admin

set -e

DEBIAN_FRONTEND=noninteractive apt-get install -y \
    sudo unzip wget zip
