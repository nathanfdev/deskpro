#!/bin/bash

echo "Ensuring log files"
sudo touch /var/log/Xvfb.log
sudo touch /var/log/firefox.log
sudo touch /var/log/selenium-hub.log
sudo touch /var/log/selenium-node.log
sudo touch /var/log/dp-install-log.log
sudo touch /var/log/php_errors.log
sudo chmod 0777 /var/log/Xvfb.log
sudo chmod 0777 /var/log/firefox.log
sudo chmod 0777 /var/log/selenium-hub.log
sudo chmod 0777 /var/log/selenium-node.log
sudo chmod 0777 /var/log/dp-install-log.log
sudo chmod 0777 /var/log/php_errors.log
echo "--> Done"

echo "Creating test database"
mysql -u root -e "CREATE USER 'deskpro'@'localhost' IDENTIFIED BY 'deskpro';"
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'deskpro'@'localhost' WITH GRANT OPTION;"
mysql -e "CREATE DATABASE deskpro;"
echo "--> Done"

echo "Creating config.php"
rm config.php
cp app/tests/travis-ci/deskpro-config.php config.php
echo "--> Done"

echo "Creating config.testing.php"
echo '<?php require("config.php");' > config.testing.php
echo "--> Done"

echo "Installing Default Tables"
php cmd.php dp:install > /var/log/dp-install-log.log
echo "--> Done"

echo "Installing Apache"
sudo apt-get update
sudo apt-get install -y apache2
sudo a2enmod actions
sudo a2enmod rewrite
echo "export PATH=/home/vagrant/.phpenv/bin:$PATH" | sudo tee -a /etc/apache2/envvars > /dev/null
cat app/tests/travis-ci/apache-php-config.txt | sudo tee /etc/apache2/conf.d/phpconfig > /dev/null
cat app/tests/travis-ci/apache-vhost-config.txt | sed -e "s,PATH,`pwd`,g" | sudo tee /etc/apache2/sites-available/default > /dev/null
sudo echo "Listen 8888" | sudo tee -a /etc/apache2/ports.conf
sudo service apache2 restart

echo "Starting xvfb"
export DISPLAY=:99
/usr/bin/Xvfb :99 -ac -screen 0 1280x800x8 > /var/log/Xvfb.log 2>&1 &
echo "--> Done"

echo "Starting firefox"
firefox > /var/log/firefox.log 2>&1 &
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