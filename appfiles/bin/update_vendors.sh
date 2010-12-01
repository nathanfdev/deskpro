#!/bin/sh

cd ..

CURRENT=`pwd`/vendor

# Symfony
cd $CURRENT/symfony && git pull origin master

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

# Pheanstalk
cd $CURRENT/pheanstalk && git pull

# Facebook
cd $CURRENT/facebook && git pull

# ZF1
cd $CURRENT
rm -rf zend1
mkdir zend1
cd zend1
rm -f ZendFramework-1.11.0-minimal.tar.gz
wget http://framework.zend.com/releases/ZendFramework-1.11.0/ZendFramework-1.11.0-minimal.tar.gz
tar zxvf ZendFramework-1.11.0-minimal.tar.gz
mv ZendFramework-1.11.0-minimal.tar.gz/* .
rmdir ZendFramework-1.11.0-minimal.tar.gz
rm -f ZendFramework-1.11.0-minimal.tar.gz