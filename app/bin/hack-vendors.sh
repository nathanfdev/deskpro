#!/bin/bash

DIR_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../../"
DIR_VENDOR=$DIR_ROOT/app/vendor

sed -i -e 's/&offsetGet/offsetGet/g' $DIR_VENDOR/zendframework/zendframework/library/Zend/Stdlib/ArrayObject.php
rm -f $DIR_VENDOR/zendframework/zendframework/library/Zend/Stdlib/ArrayObject.php-e