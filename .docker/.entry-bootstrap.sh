#!/bin/bash

## set -e: Stop on error
set -e

service mongodb start
apache2ctl start
