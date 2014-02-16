#!/bin/bash

export LC_CTYPE=C
export LANG=C

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
while [ -z $WEBROOT_DIR ]; do
    echo "Please enter Site's Root Folder:"
    read WEBROOT_DIR
done
echo

# If destination folder doesn't exist, create it or exit
# Otherwise, empty it or exit
if [ ! -d $WEBROOT_DIR ]; then
    while [[ ! $CREATE_WEBROOT_DIR =~ ^([yY][eE][sS]|[yY]|[nN][oO]|[nN])$ ]]; do
        echo "Folder $WEBROOT_DIR doesn't exist, should I create it now? [y/n]"
        read CREATE_WEBROOT_DIR
    done

    # Exit if the user doesn't want to create the destination folder
    if [[ $CREATE_WEBROOT_DIR =~ ^([nN][oO]|[nN])$ ]]; then
        echo "Exiting"
        exit 1
    else
        mkdir -p $WEBROOT_DIR

        RETVAL=$?
        if [ $RETVAL -ne 0 ]; then
            echo "Failed creating '$WEBROOT_DIR' folder"
            exit 1
        fi
    fi
else
    while [[ ! $EMPTY_WEBROOT_DIR =~ ^([yY][eE][sS]|[yY]|[nN][oO]|[nN])$ ]]; do
        echo "Folder $WEBROOT_DIR already exists, should I empty it now? [y/n]"
        read EMPTY_WEBROOT_DIR
    done

    # Exit if the user doesn't want to empty destination folder
    if [[ $EMPTY_WEBROOT_DIR =~ ^([nN][oO]|[nN])$ ]]; then
        echo "Exiting"
        exit 1
    else
        rm -rf $WEBROOT_DIR
    fi
fi
echo

TPL_DIR="./template"

# Create temporary directory
TMP_DIR=`mktemp -d ./drupal-ignite-core-XXXXXX`

# Copy template to temporary directory for processing
cp -R $TPL_DIR/ $TMP_DIR/

# Replace vendor name and site name inside files
find $TMP_DIR -type f -print0 | xargs -0 sed -i "" 's/__vendor__/'$VENDOR'/g'
find $TMP_DIR -type f -print0 | xargs -0 sed -i "" 's/__site__/'$NAME'/g'

# Rename files and directories to replace vendor name and site name
FILES=`find $TMP_DIR -name "*vendor*" -o -name "*site*"`

while [[ -n $FILES ]]; do
    for FILE in $FILES; do
        NEW_FILE=`echo $FILE | sed -e 's/vendor/'$VENDOR'/g' | sed -e 's/site/'$NAME'/g'`
        NEW_FILE_DIR=$(dirname $NEW_FILE)

        if [ ! -d $NEW_FILE_DIR ]; then
            mkdir -p $NEW_FILE_DIR
        fi

        if [ -f $FILE ] || [ -d $FILE ]; then
            mv $FILE $NEW_FILE
        fi
    done

    FILES=`find $TMP_DIR -name "*vendor*" -o -name "*site*"`
done

# Copy processed files and directories to destination folder
cp -R $TMP_DIR/ $WEBROOT_DIR/

# Clean up temporary directory
rm -rf $TMP_DIR

# Goodbye
echo
echo "Done."
echo
