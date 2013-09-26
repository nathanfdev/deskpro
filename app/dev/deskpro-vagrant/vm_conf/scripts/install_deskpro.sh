#!/bin/bash

cd /deskpro/www

cp /vagrant/vm_conf/scripts/deskpro_config.php config.php

php cmd.php dp:install --verbose --insert-initial --admin-email=CONFIG

cd /deskpro/www/web/app
npm install grunt --save-dev

chown -R vagrant:vagrant /deskpro/www
chmod -R 0777 /deskpro/www/data
chmod -R 0777 /deskpro/www/app/sys/cache