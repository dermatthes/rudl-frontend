#!/bin/bash

## set -e: Stop on error
set -e

apt-get update

# DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -q -y syslog-ng


echo "[RECIPE] apache2.sh"
DEBIAN_FRONTEND=noninteractive apt-get --no-install-recommends -y install \
    apache2 php7.0 php-mongodb composer mongodb ca-certificates

rm -R /var/www/html
ln -s /opt/www /var/www/html



echo "[RECIPE] set_timezone.sh"

TZ="Europe/Berlin"
ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone