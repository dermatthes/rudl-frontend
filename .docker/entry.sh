#!/bin/bash

set -e

echo ""
echo "+-------------------------------------------------------------------------------------------"
echo "| CONTAINER STARTUP!"
echo "+-------------------------------------------------------------------------------------------"
echo "| Running entry.sh inside container"
echo "| Paremters: $@"
echo "+-------------------------------------------------------------------------------------------"
echo ""
ls -l


echo "[BOOTSTRAP] Running .entry-bootstrap.sh..."

. /root/.entry-bootstrap.sh


if [ "$1" == "unit-test" ]
then
    echo ""
    echo "[UNIT-TEST] Running unit-tests from .docker/.unit-test.sh"
    . /root/.unit-test.sh
    echo "[DONE]"
    exit
fi

if [ "$1" == "dev" ]
then
    echo ""
    echo "[DEVELOPMENT MODE] Running /bin/bash instead of /root/.entry-loop.sh"
    echo ""
    /bin/bash
    exit
fi

echo "[LOOP] Running .entry-loop.sh..."

. /root/.entry-loop.sh



