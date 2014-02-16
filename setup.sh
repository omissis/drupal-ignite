#!/bin/bash

export LC_CTYPE=C
export LANG=C

ARGS=`getopt -l "help,name:,tld:,vendor:,docroot:" -o "h" -- "$@"`

#Bad arguments
if [ $? -ne 0 ]; then
    exit 1
fi

# A little magic
eval set -- "$ARGS"

# Now go through all the options
while [ $# -ge 1 ]; do
    case "$1" in
        --name)
            NAME=$2
            shift
            ;;
        --tld)
            TLD=$2
            shift
            ;;
        --vendor)
            VENDOR=$2
            shift
            ;;
        --docroot)
            DOCUMENT_ROOT=$2
            shift
            ;;
        -h|--help)
            echo "$(basename "$0") [-h] [--docroot --name --tld --vendor] -- Drupal Ignite installation script.

where:
    --docroot  set the value for site's document root (eg: /var/www/acme/demo)
    --name     set the value for site's name (eg: demo)
    --tld      top level domain to use without leading point (eg: com, org)
    --vendor   set the value for site's vendor (eg: acme)
    -h|--help  show this help text"
            exit 0
            ;;
        --)
            shift
            break
            ;;
    esac

    shift
done

echo
echo "=========================="
echo "Drupal Ignite Setup Script"
echo "=========================="
echo
echo "This script will guide you into setting up your Drupal Ignite installation."
echo

# Read site vendor's name
while [ -z $VENDOR ]; do
    echo "Please enter Site's Vendor:"
    read VENDOR
done
echo

# Read site's name
while [ -z $NAME ]; do
    echo "Please enter Site's Name:"
    read NAME
done
echo

# Read site's destination directory
while [ -z $DOCUMENT_ROOT ]; do
    echo "Please enter Site's Root Folder:"
    read DOCUMENT_ROOT
done
echo

# Set Top Level Domain if it hasn't been passed through Command Line
if [ -z $TLD ]; then
    TLD='com'
fi

# If destination folder doesn't exist, create it or exit
# Otherwise, empty it or exit
if [ ! -d $DOCUMENT_ROOT ]; then
    while [[ ! $CREATE_DOCUMENT_ROOT =~ ^([yY][eE][sS]|[yY]|[nN][oO]|[nN])$ ]]; do
        echo "Folder $DOCUMENT_ROOT doesn't exist, should I create it now? [y/n]"
        read CREATE_DOCUMENT_ROOT
    done

    # Exit if the user doesn't want to create the destination folder
    if [[ $CREATE_DOCUMENT_ROOT =~ ^([nN][oO]|[nN])$ ]]; then
        echo "Exiting"
        exit 1
    else
        mkdir -p $DOCUMENT_ROOT

        RETVAL=$?
        if [ $RETVAL -ne 0 ]; then
            echo "Failed creating '$DOCUMENT_ROOT' folder"
            exit 1
        fi
    fi
else
    while [[ ! $EMPTY_DOCUMENT_ROOT =~ ^([yY][eE][sS]|[yY]|[nN][oO]|[nN])$ ]]; do
        echo "Folder $DOCUMENT_ROOT already exists, should I empty it now? [y/n]"
        read EMPTY_DOCUMENT_ROOT
    done

    # Exit if the user doesn't want to empty destination folder
    if [[ $EMPTY_DOCUMENT_ROOT =~ ^([nN][oO]|[nN])$ ]]; then
        echo "Aborted execution, exiting."
        exit 1
    else
        if [ -e $DOCUMENT_ROOT ]; then
            rm -rf $DOCUMENT_ROOT/*
        fi
    fi
fi
echo

TPL_DIR="./template"

# Create temporary directory
TMP_DIR=`mktemp -d ./drupal-ignite-core-XXXXXX`

# Copy template to temporary directory for processing
cp -R $TPL_DIR/* $TMP_DIR/

# Replace strings inside files
# Using "|" instead of "/" to avoid issues with slashes in docroot path
find $TMP_DIR -type f -print0 | xargs -0 sed -i"" -e 's|__docroot__|'$DOCUMENT_ROOT'|g'
find $TMP_DIR -type f -print0 | xargs -0 sed -i"" -e 's/__vendor__/'$VENDOR'/g'
find $TMP_DIR -type f -print0 | xargs -0 sed -i"" -e 's/__site__/'$NAME'/g'
find $TMP_DIR -type f -print0 | xargs -0 sed -i"" -e 's/__tld__/'$TLD'/g'

# Rename files and directories to replace vendor name and site name
FILES=`find $TMP_DIR -name "*__vendor__*" -o -name "*__site__*"`

while [[ -n $FILES ]]; do
    for FILE in $FILES; do
        NEW_FILE=`echo $FILE | sed -e 's/__vendor__/'$VENDOR'/g' | sed -e 's/__site__/'$NAME'/g'`
        NEW_FILE_DIR=$(dirname $NEW_FILE)

        if [ ! -d $NEW_FILE_DIR ]; then
            mkdir -p $NEW_FILE_DIR
        fi

        if [ -f $FILE ] || [ -d $FILE ]; then
            mv $FILE $NEW_FILE
        fi
    done

    FILES=`find $TMP_DIR -name "*__vendor__*" -o -name "*__site__*"`
done

# Copy processed files and directories to destination folder
cp -R $TMP_DIR/* $DOCUMENT_ROOT/

# Clean up temporary directory
if [ -e $TMP_DIR ]; then
    rm -rf $TMP_DIR
fi

# Goodbye
echo
echo "Done."
echo
