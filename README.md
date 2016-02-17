# Installing DeskPRO

## Init

After checkout, run the init script:

    $ cd deskpro
    $ dev/bin/init-project-dev

This will install composer/npm dependencies and build asset files.

## Install

Install the database with the install wizard:

    $ bin/install --install-source dev

## Install without wizard (advanced)

You can bypass the wizard and write your own configuration files. This is useful if you
already have config you want to keep (e.g. it is common to re-install a fresh DB while developing).

1. Copy the default dev config from `dev/config_dev_new/*` to `/config`

2. Edit them as needed (particularly `config.database.php` and `config.paths.php`).

2. Run the install command but skip the wizard:

    bin/install --dev --user 'your@email.com, your_password'

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
