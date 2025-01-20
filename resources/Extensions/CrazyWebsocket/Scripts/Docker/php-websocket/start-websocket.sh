#!/bin/sh
# File: start-websocket.sh

# Enable debugging (optional)
set -e

# check composer
php ./vendor/kzarshenas/crazyphp/bin/CrazyWebsocket -y run

# Sleep
sleep 50