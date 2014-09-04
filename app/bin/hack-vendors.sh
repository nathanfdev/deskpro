#!/bin/bash

DIR_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../../"
DIR_VENDOR=$DIR_ROOT/app/vendor

sed -i -e 's/&offsetGet/offsetGet/g' $DIR_VENDOR/zendframework/zendframework/library/Zend/Stdlib/ArrayObject.php
rm -f $DIR_VENDOR/zendframework/zendframework/library/Zend/Stdlib/ArrayObject.php-e

cp $DIR_VENDOR/zendframework/zendframework/library/Zend/Ldap/Node.php $DIR_VENDOR/zendframework/zendframework/library/Zend/Ldap/Node.php.orig
cat $DIR_VENDOR/zendframework/zendframework/library/Zend/Ldap/Node.php.orig | php -r 'echo preg_replace('\''/if \(\!Dn\:\:isChildOf\(\$this\-\>_getDn\(\), \$ldap\-\>getBaseDn\(\)\)\) \{.*?\}\s*/s'\'', "/* DESKPRO EDIT: Removed isChildOf check */\n\n        ", stream_get_contents(STDIN));' > $DIR_VENDOR/zendframework/zendframework/library/Zend/Ldap/Node.php
rm $DIR_VENDOR/zendframework/zendframework/library/Zend/Ldap/Node.php.orig

php DIR_ROOT/app/bin/build/build-vendors-mutate.php