#!/bin/bash


## set -e: Stop on error
set -e

sed -i 's/bind_ip = 127.0.0.1/bind_ip = /g' /etc/mongodb.conf

sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
a2enmod rewrite

#if [ "$1" != "dev" ]
#then
#    cd /opt/
#    composer update
#fi

