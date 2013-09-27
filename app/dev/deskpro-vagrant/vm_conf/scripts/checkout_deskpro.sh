#!/bin/bash

###DP_CONFIG_BEGIN###
REPOS_URL='https://your_username:your_password@github.com/DeskPRO/DeskPRO.git'
###DP_CONFIG_END###

if [ ! -d "/deskpro/www" ]; then
	mkdir -p /deskpro/www
fi

cd /deskpro/www
git clone $REPOS_URL .

chown --silent -R vagrant:vagrant /deskpro/www
chmod --silent -R 0777 /deskpro/www/app/sys/cache
chmod --silent -R 0777 /deskpro/www/data