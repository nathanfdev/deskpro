#!/bin/bash

DIR_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../../"

echo ">> Cleaning"
rm -rf $DIR_ROOT/app/vendor
rm -rf $DIR_ROOT/web/app-build
rm -rf $DIR_ROOT/web/loader-build
rm -rf $DIR_ROOT/web/bower_components
rm -rf $DIR_ROOT/web/node_modules
echo ".. done"
echo

echo ">> Installing vendors with composer"
cd $DIR_ROOT/app
composer install --ignore-platform-reqs -o
echo ".. done"
echo

echo ">> Hacking vendors"
$DIR_ROOT/app/bin/hack-vendors.sh
echo ".. done"

echo ">> Installing web dependencies"
cd $DIR_ROOT/web
npm install --save
bower install --config.interactive=false --allow-root
echo ".. done"

echo ">> Building web assets"
gulp
echo ".. done"
echo
