#!/bin/sh

cd ..

CURRENT=`pwd`/vendor

# Symfony
cd $CURRENT/symfony && git pull

# Doctrine ORM
cd $CURRENT/doctrine-orm && git pull

# Doctrine DBAL
cd $CURRENT/doctrine-dbal && git pull

# Doctrine common
cd $CURRENT/doctrine-common && git pull

# Doctrine migrations
cd $CURRENT/doctrine-migrations && git pull

# Swiftmailer
cd $CURRENT/swiftmailer && git pull

# Twig
cd $CURRENT/twig && git pull

# Zend Framework
cd $CURRENT/zend && git pull
