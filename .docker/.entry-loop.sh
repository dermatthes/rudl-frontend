#!/bin/bash

## set -e: Stop on error
set -e

echo "Waiting 120sec for mongodb to become ready..."
sleep 120

/opt/bin/rudlconsole --rudl --server

