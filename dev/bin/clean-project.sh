#!/bin/bash

DIR_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../.."

rm -rf $DIR_ROOT/app/vendor
rm -rf $DIR_ROOT/web/app-build
rm -rf $DIR_ROOT/web/loader-build
rm -rf $DIR_ROOT/web/bower_components
rm -rf $DIR_ROOT/web/node_modules
rm -rf $DIR_ROOT/pub/node_modules
rm -rf $DIR_ROOT/pub/build

rm -rf ../../app/sys/cache/*/
rm -rf $DIR_ROOT/data/http_cache/*/

rm -f $DIR_ROOT/app/src/Application/InstallBundle/Data/schema.php
rm -f $DIR_ROOT/pub/src/DeskPRO/Bundle/AgentBundle/AgentApp_Reducers.js
