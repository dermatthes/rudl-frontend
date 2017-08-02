#!/bin/bash


## Load .env Properties
. ./.env


echo ""
echo "------------------------------------------------------------------------------------"
echo " + Loading from .env..."
echo " + Starte Projekt: $PROJECT_NAME"
echo " -> Registry Path: $REGISTRY_URL"
echo "------------------------------------------------------------------------------------"
echo ""




echo "Using composer file '$COMPOSER_FILE'..."

if [[ ! -e $COMPOSER_FILE ]]
then
    echo "Composer file $COMPOSER_FILE not existing."
    exit 1
fi

if [[ $1 == "shell" ]]
then
    docker-compose -f $COMPOSER_FILE exec $MAIN_SERVICE /bin/bash
    exit
fi

## Lokales Entwickeln / gitlab-ci testing:

set +e
docker rm  $PROJECT_NAME
set -e



echo "Starting image in interactive mode... (Parameters (#$#): $@)";
docker-compose -f $COMPOSER_FILE build


if (( $# < 1 ))
then
    CMD="docker-compose -f $COMPOSER_FILE up"
    echo "[NO PARAMETERS] Running '$CMD'..."
    eval $CMD
else
    echo "Starting manual service: $MAIN_SERVICE from Composer-File $COMPOSER_FILE (defined in .env)..."
    CMD="docker-compose -f $COMPOSER_FILE run -v '$PWD/:/opt/' --service-ports $MAIN_SERVICE $1 $2 $3 $4 $5"
    echo "[WITH PARAMETERS] Running '$CMD'..."
    eval $CMD
fi


echo "Image closed...";