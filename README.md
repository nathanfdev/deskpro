# Installing DeskPRO

## Init

After checkout, run the init script:

    $ cd deskpro
    $ dev/bin/init-project-dev

This will install composer/npm dependencies and build asset files.

## Install

Install the database with the install wizard:

    $ bin/install --install-source dev

## Server

You'll need a web server with the docroot at www/. Or you can just use PHP's web server:

    $ php -S 0.0.0.0:9090 dev/php-server/routing.php


# Running tests

    $ cd app/BUILD/tests
    $ cp config/config.all.dist.php config/config.all.php
    $ vim config/config.all.php # edit db details and anything else as necessary
    $ bin/run-tests-quick

# Wiki

| Wiki     | http://wiki.deskprodev.com/ |
|----------|-----------------------------|
| User     | reader                      |
| Password | wikireader                  |
