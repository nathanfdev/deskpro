#!/bin/sh

cd ../../

# initialization
if [ -d "vendor" ]; then
  rm -rf appfiles/vendor/*
else
  mkdir appfiles/vendor
fi

# Symfony
git submodule add http://github.com/symfony/symfony.git appfiles/vendor/symfony

# Doctrine ORM
git submodule add git://github.com/doctrine/doctrine2.git appfiles/vendor/doctrine-orm

# Doctrine DBAL
git submodule add git://github.com/doctrine/dbal.git appfiles/vendor/doctrine-dbal

# Doctrine Common
git submodule add git://github.com/doctrine/common.git appfiles/vendor/doctrine-common

# Doctrine migrations
git submodule add git://github.com/doctrine/migrations.git appfiles/vendor/doctrine-migrations

# Swiftmailer
git submodule add git://github.com/swiftmailer/swiftmailer.git appfiles/vendor/swiftmailer

# Twig
git submodule add git://github.com/fabpot/Twig.git appfiles/vendor/twig

# Zend Framework
git submodule add git://github.com/zendframework/zf2.git appfiles/vendor/zend

# Pheanstalk
git submodule add git://github.com/pda/pheanstalk.git appfiles/vendor/pheanstalk

# Facebook
git submodule add http://github.com/facebook/php-sdk.git appfiles/vendor/facebook

# ZF1
cd appfiles/vendor
mkdir zend1
cd zend1
wget http://framework.zend.com/releases/ZendFramework-1.10.8/ZendFramework-1.10.8-minimal.tar.gz
tar zxvf ZendFramework-1.10.8-minimal.tar.gz
mv ZendFramework-1.10.8-minimal/* .
rmdir ZendFramework-1.10.8-minimal
rm -f ZendFramework-1.10.8-minimal.tar.gz