#!/bin/bash

cd /deskpro/www

cp /vm_local/init/conf/deskpro_config.php config.php
cp /vm_local/init/conf/deskpro_config.testing.php config.testing.php

php cmd.php dp:install --verbose

# New adminui currently only branch that uses grunt
git checkout feature/adminui
cd /deskpro/www/web/app
npm install --save-dev

# Then after that, pop them back into the default develop branch
cd /deskpro/www
git checkout develop

chown --silent -R vagrant:vagrant /deskpro/www
chmod --silent -R 0777 /deskpro/www/data
chmod --silent -R 0777 /deskpro/www/app/sys/cache
