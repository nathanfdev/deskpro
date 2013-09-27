#!/bin/bash

cd /deskpro/www

cp /vagrant/vm_conf/scripts/deskpro_config.php config.php

php cmd.php dp:install --verbose

cd /deskpro/www/web/app
npm install grunt --save-dev

chown --silent -R vagrant:vagrant /deskpro/www
chmod --silent -R 0777 /deskpro/www/data
chmod --silent -R 0777 /deskpro/www/app/sys/cache