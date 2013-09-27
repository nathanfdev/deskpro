#!/bin/bash

fromdos /vagrant/vm_conf/scripts/checkout_deskpro.sh
fromdos /vagrant/vm_conf/scripts/install_deskpro.sh

sudo apt-get update
sudo apt-get install --yes python-software-properties
sudo add-apt-repository --yes ppa:chris-lea/node.js
sudo apt-get update
sudo apt-get install --yes nodejs

npm install -g grunt-cli
npm install -g bower

mysql -uroot -e "CREATE DATABASE IF NOT EXISTS deskpro; UPDATE mysql.user SET password = PASSWORD('deskpro') WHERE User = 'root'; FLUSH PRIVILEGES;"

if [ ! -d /deskpro/www/app ];then
	/bin/bash /vagrant/vm_conf/scripts/checkout_deskpro.sh
fi

if [ ! -f /deskpro/www/config ];then
	/bin/bash /vagrant/vm_conf/scripts/install_deskpro.sh
fi

service nginx restart