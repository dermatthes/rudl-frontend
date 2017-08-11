#!/bin/bash

## set -e: Stop on error
set -e

apt-get update

# DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -q -y syslog-ng


echo "[RECIPE] apache2.sh"
DEBIAN_FRONTEND=noninteractive apt-get --no-install-recommends -y install \
    apache2 php7.0 apache2-mod-php7.0 php-mongodb composer ca-certificates git


# Install MongoDB 3.4 (php-mongodb broken in 17.04)
apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv EA312927
echo "deb http://repo.mongodb.org/apt/ubuntu xenial/mongodb-org/3.2 multiverse" | tee /etc/apt/sources.list.d/mongodb-org-3.2.list
apt-get update
apt-get install -y mongodb-org gosu


rm -R /var/www/html
ln -s /opt/www /var/www/html



echo "[RECIPE] set_timezone.sh"

TZ="Europe/Berlin"
ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone