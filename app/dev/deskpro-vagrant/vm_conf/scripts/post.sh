#!/bin/bash

fromdos /vm_conf/scripts/checkout_deskpro.sh
fromdos /vm_conf/scripts/install_deskpro.sh

echo "Increasing apc memory"
echo "" >> /etc/php5/conf.d/apc.ini
echo "apc.shm_size = 100M" >> /etc/php5/conf.d/apc.ini

echo "Installing PHPUnit"
sudo pear config-set auto_discover 1
sudo pear install pear.phpunit.de/PHPUnit

echo "Install behat"
mkdir -p /usr/local/bin/behat-src
cd /usr/local/bin/behat-src
wget https://github.com/downloads/Behat/Behat/behat.phar

echo "#!/bin/bash" > /usr/local/bin/behat
echo "" >> /usr/local/bin/behat
echo 'php /usr/local/bin/behat/behat.phar "$@"' >> /usr/local/bin/behat
chmod 0755 /usr/local/bin/behat

echo "Installing nodejs"
sudo apt-get update
sudo apt-get install --yes python-software-properties
sudo add-apt-repository --yes ppa:chris-lea/node.js
sudo apt-get update
sudo apt-get install --yes nodejs

echo "Installing grunt"
npm install -g grunt-cli

echo "Installing bower"
npm install -g bower

echo "Setting MySQL root password to 'deskpro' and creating initial 'deskpro' database"
mysql -uroot -e "CREATE DATABASE IF NOT EXISTS deskpro; GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' IDENTIFIED BY 'deskpro' WITH GRANT OPTION; GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'deskpro' WITH GRANT OPTION; FLUSH PRIVILEGES;"

echo "Checking out DesKPRO files"
if [ ! -d /deskpro/www/app ];then
	/bin/bash /vm_conf/scripts/checkout_deskpro.sh
fi

echo "Installing DeskPRO"
if [ ! -f /deskpro/www/config ];then
	/bin/bash /vm_conf/scripts/install_deskpro.sh
fi

echo "Installing DeskPRO cron job"
echo "* * * * * www-data php /deskpro/www/cron.php" >> /etc/crontab

echo "Restarting webserver"
service nginx restart
service php5-fpm restart