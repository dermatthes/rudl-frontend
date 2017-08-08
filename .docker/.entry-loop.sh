#!/bin/bash

## set -e: Stop on error
set -e

echo "Waiting 15sec for mongodb to become ready..."
sleep 15



/opt/bin/rudl_log_emitter
