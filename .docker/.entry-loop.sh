#!/bin/bash

## set -e: Stop on error
set -e

echo "Waiting 60sec for mongodb to become ready..."
sleep 60



/opt/bin/rudl_log_emitter
