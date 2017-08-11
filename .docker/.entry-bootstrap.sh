#!/bin/bash

## set -e: Stop on error
set -e

gosu mongodb mongod --fork --config /etc/mongod.conf
apache2ctl start


