#!/bin/bash

DIR_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../../"
DIR_VENDOR=$DIR_ROOT/app/vendor
DIR_VENDOR_SRC=$DIR_ROOT/app/vendor-src

sed -i -e 's/&offsetGet/offsetGet/g' $DIR_VENDOR/zendframework/zend-stdlib/src/ArrayObject.php
rm -f $DIR_VENDOR/zendframework/zend-stdlib/src/ArrayObject.php-e

cp $DIR_VENDOR/zendframework/zend-ldap/src/Node.php $DIR_VENDOR/zendframework/zend-ldap/src/Node.php.orig
cat $DIR_VENDOR/zendframework/zend-ldap/src/Node.php.orig | php -r 'echo preg_replace('\''/if \(\!Dn\:\:isChildOf\(\$this\-\>_getDn\(\), \$ldap\-\>getBaseDn\(\)\)\) \{.*?\}\s*/s'\'', "/* DESKPRO EDIT: Removed isChildOf check */\n\n        ", stream_get_contents(STDIN));' > $DIR_VENDOR/zendframework/zend-ldap/src/Node.php
rm $DIR_VENDOR/zendframework/zend-ldap/src/Node.php.orig

if ! grep -q DESKPRO_MODIFIED $DIR_VENDOR_SRC/libphonenumber/src/libphonenumber/PhoneNumberUtil.php; then
    cat $DIR_ROOT/app/bin/build/data/libphonenumber_mb_compat.php | sed 's/<?php\s*//' >> $DIR_VENDOR_SRC/libphonenumber/src/libphonenumber/PhoneNumberUtil.php
fi

php $DIR_ROOT/app/bin/build/build-vendors-mutate.php