#!/bin/bash

DIR_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../.."
TARGET_DIR="$DIR_ROOT/www/assets/BUILD/docs/third-party"

echo "Generating license list from composer"
cd $DIR_ROOT/app/BUILD
composer licenses --format=json \
    | sed 's/GNU General Public License/GPL/' \
    | sed 's/Apache2/Apache 2.0/' \
    > $TARGET_DIR/composer-lic-info.json
echo ".. done"

echo "Generating license list from npm"
cd $DIR_ROOT/www/assets/BUILD/pub
npm run --silent license-checker \
    | sed 's/"\/.*\/www\/assets\//"\/www\/assets\//' \
    | sed 's/MIT\*/MIT/' \
    | sed 's/Apache\*/Apache/' \
    | sed 's/BSD\*/MIT/' \
    > $TARGET_DIR/npm-lic-info.json
echo ".. done"

echo "Generating license list from legacy npm"
cd $DIR_ROOT/www/assets/BUILD/web
npm run --silent license-checker \
    | sed 's/"\/.*\/www\/assets\//"\/www\/assets\//' \
    | sed 's/MIT\*/MIT/' \
    | sed 's/Apache\*/Apache/' \
    | sed 's/BSD\*/BSD/' \
    > $TARGET_DIR/legacy-npm-lic-info.json
echo ".. done"