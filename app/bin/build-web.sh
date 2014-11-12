#!/bin/sh

DIR_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../../"
cd $DIR_ROOT/web

echo ">> Installing npm deps"
npm install --save
echo " .. done"
echo

echo ">> installing bower deps"
bower install
echo ".. done"
echo

echo ">> building"
gulp prod
echo ".. done"
echo