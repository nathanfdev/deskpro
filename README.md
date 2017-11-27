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

## Asset Server

You'll need to also run the asset server which serves compiled versions of CSS/JS.

    cd /path/to/deskpro/www/assets/BUILD/pub
    npm run dev

The asset server is enabled by default in `config/config.paths.php`. If you disable it, then assets
must be manually built to the normal filesystem:

    cd /path/to/deskpro/www/assets/BUILD/pub
    npm run prod

It's almost always desirable to use the asset server in development though. It will set up a file watcher
and automatically re-compile any changes you make.

If you want to change old/legacy assets, you can run the old asset dev watcher (note the dir is web/):

    cd /path/to/deskpro/www/assets/BUILD/web
    npm run prod

# Running tests

    $ cd app/BUILD/tests
    $ cp config/config.all.dist.php config/config.all.php
    $ vim config/config.all.php # edit db details and anything else as necessary
    $ bin/run-tests-quick

    # Run a specific API test
    bin/behat --profile=api features/api/tickets/messages/ticket_messages.feature

    # Run a specific API feature
    bin/behat --profile=api --name 'Person primary email CRUD' features/api/tickets/messages/ticket_messages.feature

    # Run a specific phpunit test
    bin/phpunit phpunit/DpTest/Orb/Util/StringsTest.php

    # Run a specific spec test (not used much anymore)
    bin/phpspec run spec/DeskPRO/Bundle/AppBundle/EventListener/SecurityHeadersResponseListenerSpec.php

# Wiki

| Wiki     | http://wiki.deskprodev.com/ |
|----------|-----------------------------|
| User     | reader                      |
| Password | dpreader2016                |
