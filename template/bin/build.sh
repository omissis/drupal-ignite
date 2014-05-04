if [ -z $1 ]; then
    echo "You must specify a phing target such as 'loc-app' or 'dev-app'."
    exit 1
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=$DIR
php $DIR/composer.phar install --verbose
$DIR/phing $1 -verbose
