#!/bin/bash

echo "Creating test database"
mysql -e "CREATE DATABASE deskpro;"
echo "--> Done"

echo "Creating config.php"
rm config.php
cp config.new.php config.php

sed -i tmp "s/define('DP_DATABASE_HOST', 'localhost')/define('DP_DATABASE_HOST', '127.0.0.1')/g" config.php
sed -i tmp "s/define('DP_DATABASE_USER', 'root')/define('DP_DATABASE_USER', 'travis')/" config.php

echo '$DP_CONFIG['"'"'debug'"'"']['"'"'dev'"'"']                     = true;' >> config.php
echo '$DP_CONFIG['"'"'debug'"'"']['"'"'raw_assets'"'"']              = array('"'"'all'"'"');' >> config.php
echo '$DP_CONFIG['"'"'debug'"'"']['"'"'no_report_errors'"'"']        = true;' >> config.php
echo '$DP_CONFIG['"'"'cache'"'"']['"'"'page_cache'"'"']['"'"'enable'"'"']    = false;' >> config.php
echo '$DP_CONFIG['"'"'debug'"'"']['"'"'mail'"'"']['"'"'save_to_file'"'"']    = true;' >> config.php
echo '$DP_CONFIG['"'"'debug'"'"']['"'"'mail'"'"']['"'"'enable_mail_log'"'"'] = true;' >> config.php
echo '$DP_CONFIG['"'"'debug'"'"']['"'"'mail'"'"']['"'"'disable_send'"'"']    = true;' >> config.php
echo '$DP_CONFIG['"'"'rewrite_urls'"'"'] = true;' >> config.php
echo '$DP_CONFIG['"'"'SETTINGS'"'"'] = array();' >> config.php
echo '$DP_CONFIG['"'"'SETTINGS'"'"']['"'"'core.use_mail_queue'"'"']    = '"'"'never'"'"';' >> config.php
echo '$DP_CONFIG['"'"'SETTINGS'"'"']['"'"'core.show_share_widget'"'"'] = false;' >> config.php
echo '$DP_CONFIG['"'"'SETTINGS'"'"']['"'"'core.use_gravatar'"'"']      = false;' >> config.php

echo "--> Done"

echo "Creating config.testing.php"
echo '<?php require("config.php");' > config.testing.php
echo "--> Done"

echo "Installing Apache"
sudo apt-get install -y apache2
sudo a2enmod actions
sudo a2enmod rewrite
echo "export PATH=/home/vagrant/.phpenv/bin:$PATH" | sudo tee -a /etc/apache2/envvars > /dev/null
echo cat app/tests/travis-ci/apache-php-config.txt | sudo tee /etc/apache2/conf.d/phpconfig > /dev/null
echo cat app/tests/travis-ci/apache-vhost-config.txt | sed -e "s,PATH,`pwd`,g" | sudo tee /etc/apache2/sites-available/default > /dev/null
echo "Listen 8888" >> /etc/apache2/ports.conf
sudo service apache2 restart

echo "Starting xvfb"
export DISPLAY=:99
/usr/bin/Xvfb :99 -ac -screen 0 1280x800x8
echo "--> Done"

echo "Starting firefox"
firefox
echo "--> Done"

echo "Downloading Selenium"
wget http://selenium.googlecode.com/files/selenium-server-standalone-2.31.0.jar
echo "--> Done"

echo "Starting Selenium Hub"
java -jar /usr/local/bin/selenium/selenium-server-standalone-2.31.0.jar -role hub > /var/log/selenium-hub.log 2>&1 &
echo "."
sleep 3
echo "--> Done"

echo "Starting Selenium Node"
java -jar /usr/local/bin/selenium/selenium-server-standalone-2.31.0.jar -role node -hub http://localhost:4444/grid/register > /var/log/selenium-node.log 2>&1 &
echo "."
sleep 3
echo "--> Done"