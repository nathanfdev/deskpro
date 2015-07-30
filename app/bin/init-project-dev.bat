#!/bin/bash

cd %~dp0
cd ../../

echo ">> Cleaning"
rmdir /s /q .\app\vendor
rmdir /s /q .\web\app-build
rmdir /s /q .\web\loader-build
rmdir /s /q .\web\bower_components
rmdir /s /q .\web\node_modules
echo ".. done"
echo

echo ">> Installing vendors with composer"
cd %~dp0
cd ../../app
composer install --ignore-platform-reqs -o
echo ".. done"
echo

echo ">> Hacking vendors"
cd %~dp0
cd ../../
php .\app\bin\build\build-vendors-mutate.php
echo ".. done"

echo ">> Installing web dependencies"
cd %~dp0
cd ../../web
npm install --save --save-dev
bower install --config.interactive=false --allow-root --save-dev
echo ".. done"

echo ">> Building web assets"
gulp
echo ".. done"
echo

echo ">> Installing new web dependencies (dev)"
cd %~dp0
cd ../../pub
npm install --save-dev
echo ".. done"

echo ">> Building new web assets"
bin/gulp
echo ".. done"
echo
