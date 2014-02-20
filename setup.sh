#!/bin/bash

slugify () {
    MAX_LENGTH="${2:-48}"

    SLUG="$({
        tr '[A-Z]' '[a-z]' | tr -cs '[[:alnum:]]' '_'
    } <<< "$1")"
    SLUG="${SLUG##_}"
    SLUG="${SLUG%%_}"
    SLUG="${SLUG:0:$MAX_LENGTH}"

    echo $SLUG
}

lowercase () {
    echo $1 | tr '[:upper:]' '[:lower:]'
}

print_header () {
    echo
    echo "=========================="
    echo "Drupal Ignite Setup Script"
    echo "=========================="
    echo
    echo "This script will guide you into setting up your Drupal Ignite installation."
    echo
}

print_footer () {
    echo
    echo "Done."
    echo
}

# Bad arguments
if [ $? -ne 0 ]; then
    exit 1
fi

# Go through all the options
while [ $# -ge 1 ]; do
    case "$1" in
        --docroot)
            DOCUMENT_ROOT=$2
            shift
            ;;
        --docroot=*)
            DOCUMENT_ROOT=${1#*=}        # Delete everything up till "="
            shift
            ;;
        --domain)
            DOMAIN=$2
            shift
            ;;
        --domain=*)
            DOMAIN=${1#*=}        # Delete everything up till "="
            shift
            ;;
        --name)
            SITE=$2
            shift
            ;;
        --name=*)
            SITE=${1#*=}        # Delete everything up till "="
            shift
            ;;
        -h|--help|-\?)
            echo "$(basename "$0") [-h] [--docroot --domain --name] -- Drupal Ignite installation script.

where:
    --docroot     set the value for site's document root (eg: /var/www/acme/demo)
    --domain      set the value for site's domain (eg: demo.acme.com)
    --name        set the value for site's name (eg: acme demo)
    -h|--help|-?  show this help text"
            exit 0
            ;;
        *)
            shift
            ;;
    esac
done

# Print welcome header
print_header

# Read site's name
while [ -z $SITE ]; do
    echo "Please enter Site's Name:"
    read SITE
done
echo

# Read site's destination directory
while [ -z $DOCUMENT_ROOT ]; do
    echo "Please enter Site's Root Folder:"
    read DOCUMENT_ROOT
done
echo

# Read site's domain
while [ -z $DOMAIN ]; do
    echo "Please enter Site's Domain:"
    read DOMAIN
done
echo

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

# Create a safer database name
DATABASE=`slugify $SITE`

# Create a safer site name
SITE=`lowercase $SITE`

# Set template directory
TPL_DIR="./template"

# Create temporary directory
TMP_DIR=`mktemp -d ./drupal-ignite-core-XXXXXX`

# Copy template to temporary directory for processing
cp -R $TPL_DIR/* $TMP_DIR/

# Replace strings inside files
# Using "|" instead of "/" to avoid issues with slashes in docroot path
find $TMP_DIR -type f -print0 | xargs -0 sed -e 's/__database__/'$DATABASE'/g' -i ""
find $TMP_DIR -type f -print0 | xargs -0 sed -e 's|__docroot__|'$DOCUMENT_ROOT'|g' -i ""
find $TMP_DIR -type f -print0 | xargs -0 sed -e 's/__domain__/'$DOMAIN'/g' -i ""
find $TMP_DIR -type f -print0 | xargs -0 sed -e 's/__site__/'$SITE'/g' -i ""

# Rename files and directories to replace site name
FILES=`find $TMP_DIR -name "*__site__*"`

while [[ -n $FILES ]]; do
    for FILE in $FILES; do
        NEW_FILE=`echo $FILE | sed -e 's/__site__/'$SITE'/g'`
        NEW_FILE_DIR=$(dirname $NEW_FILE)

        if [ ! -d $NEW_FILE_DIR ]; then
            mkdir -p $NEW_FILE_DIR
        fi

        if [ -f $FILE ] || [ -d $FILE ]; then
            mv $FILE $NEW_FILE
        fi
    done

    FILES=`find $TMP_DIR -name "*__site__*"`
done

# Copy processed files and directories to destination folder
cp -R $TMP_DIR/* $DOCUMENT_ROOT/

# Clean up temporary directory
if [ -e $TMP_DIR ]; then
    rm -rf $TMP_DIR
fi

# Temporarily switch to document root directory to download and execute Composer
cd $DOCUMENT_ROOT

# Download composer
php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=bin

# Install dependencies
php bin/composer.phar install

# Return back to previous directory
cd -

# Goodbye
print_footer
