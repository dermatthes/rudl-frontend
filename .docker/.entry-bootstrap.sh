#!/bin/bash

## set -e: Stop on error
set -e

chown -R mongodb /var/lib/mongodb

gosu mongodb mongod --fork --config /etc/mongod.conf
apache2ctl start


