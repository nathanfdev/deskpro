#!/bin/bash

echo "Ensuring log files"
sudo touch /var/log/Xvfb.log
sudo touch /var/log/firefox.log
sudo touch /var/log/selenium-hub.log
sudo touch /var/log/selenium-node.log
sudo touch /var/log/php_errors.log
sudo chmod 0777 /var/log/Xvfb.log
sudo chmod 0777 /var/log/firefox.log
sudo chmod 0777 /var/log/selenium-hub.log
sudo chmod 0777 /var/log/selenium-node.log
sudo chmod 0777 /var/log/php_errors.log
echo "--> Done"

echo "Creating test database"
mysql -e "CREATE DATABASE deskpro;"
mysql -u root -e "CREATE USER 'deskpro'@'localhost' IDENTIFIED BY 'deskpro'; CREATE USER 'deskpro'@'%' IDENTIFIED BY 'deskpro';"
mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO 'deskpro'@'localhost' WITH GRANT OPTION; GRANT ALL PRIVILEGES ON *.* TO 'deskpro'@'%' WITH GRANT OPTION; FLUSH PRIVILEGES;"
mysql -u root -e "SET GLOBAL wait_timeout=600;"
echo "--> Done"

echo "Creating config.php"
rm config.php
cp app/testing/travis-ci/deskpro-config.php config.php
echo "--> Done"

echo "Creating config.testing.php"
cp app/testing/travis-ci/deskpro-config.php config.testing.php
echo "--> Done"

echo "Cleaning test env"
app/testing/bin/reset-data
echo "--> Done"

echo "Ensuring permissions"
chmod -R 0777 data
chmod -R 0777 app/sys/cache
echo "--> Done"

echo "Installing Apache"
sudo apt-get update
sudo apt-get install -y apache2 libapache2-mod-fastcgi
sudo a2enmod actions
sudo a2enmod rewrite
echo "export PATH=/home/vagrant/.phpenv/bin:$PATH" | sudo tee -a /etc/apache2/envvars > /dev/null
cat app/testing/travis-ci/apache-php-config.txt | sudo tee /etc/apache2/conf.d/phpconfig > /dev/null
cat app/testing/travis-ci/apache-vhost-config.txt | sed -e "s,PATH,`pwd`,g" | sudo tee /etc/apache2/sites-available/default > /dev/null
echo "Listen 8888" | sudo tee -a /etc/apache2/ports.conf
sudo service apache2 restart
sudo cat /etc/php5/fpm/pool.d/www.conf

echo "Starting xvfb"
export DISPLAY=:99
/usr/bin/Xvfb :99 -ac -screen 0 1280x800x8 > /var/log/Xvfb.log 2>&1 &
echo "--> Done"

echo "Starting firefox"
firefox > /var/log/firefox.log 2>&1 &
echo "--> Done"

echo "Downloading Selenium"
wget -O /tmp/selenium-server-standalone-2.38.0.jar http://selenium.googlecode.com/files/selenium-server-standalone-2.38.0.jar
echo "--> Done"

echo "Starting Selenium Hub"
java -mx256m -jar /tmp/selenium-server-standalone-2.38.0.jar -role hub > /var/log/selenium-hub.log 2>&1 &
echo "."
sleep 3
echo "--> Done"

echo "Starting Selenium Node"
java -mx256m -jar /tmp/selenium-server-standalone-2.38.0.jar -role node -hub http://localhost:4444/grid/register > /var/log/selenium-node.log 2>&1 &
echo "."
sleep 3
echo "--> Done"