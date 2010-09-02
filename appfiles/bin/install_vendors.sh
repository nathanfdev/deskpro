#!/bin/sh

cd ..

# initialization
if [ -d "vendor" ]; then
  rm -rf vendor/*
else
  mkdir vendor
fi

# Symfony
git submodule add http://github.com/symfony/symfony.git vendor/symfony

# Doctrine ORM
git submodule add git://github.com/doctrine/doctrine2.git vendor/doctrine-orm

# Doctrine DBAL
git submodule add git://github.com/doctrine/dbal.git vendor/doctrine-dbal

# Doctrine Common
git submodule add git://github.com/doctrine/common.git vendor/doctrine-common

# Doctrine migrations
git submodule add git://github.com/doctrine/migrations.git vendor/doctrine-migrations

# Swiftmailer
git submodule add git://github.com/swiftmailer/swiftmailer.git vendor/swiftmailer

# Twig
git submodule add git://github.com/fabpot/Twig.git vendor/twig

# Zend Framework
git submodule add git://github.com/zendframework/zf2.git vendor/zend
