#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo ""
echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
echo "Error Detected - Here are some interesting log files"
echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"

if [ -e $SCRIPT_DIR/../../../data/logs/error.log ] ; then
	echo ""
	echo "[DeskPRO] error.log"
	echo "======================================================="
	echo ""
	cat $SCRIPT_DIR/../../../data/logs/error.log
fi

if [ -e $SCRIPT_DIR/../../../data/logs/install.log ] ; then
	echo ""
	echo "[DeskPRO] install.log"
	echo "======================================================="
	echo ""
	cat $SCRIPT_DIR/../../../data/logs/install.log
fi

if [ -e /var/log/php_errors.log ] ; then
	echo ""
	echo "[System] /var/log/php_errors.log"
	echo "======================================================="
	echo ""
	cat /var/log/php_errors.log
fi

if [ -e /var/log/firefox.log ] ; then
	echo ""
	echo "[System] /var/log/firefox.log"
	echo "======================================================="
	echo ""
	cat /var/log/firefox.log
fi

if [ -e /var/log/Xvfb.log ] ; then
	echo ""
	echo "[System] /var/log/Xvfb.log"
	echo "======================================================="
	echo ""
	cat /var/log/Xvfb.log
fi

if [ -e /var/log/selenium-hub.log ] ; then
	echo ""
	echo "[System] /var/log/selenium-hub.log"
	echo "======================================================="
	echo ""
	cat /var/log/selenium-hub.log
fi

if [ -e /var/log/selenium-node.log ] ; then
	echo ""
	echo "[System] /var/log/selenium-node.log"
	echo "======================================================="
	echo ""
	cat /var/log/selenium-node.log
fi

if [ -e /var/log/apache2/access.log ] ; then
	echo ""
	echo "[System] /var/log/apache2/access.log"
	echo "======================================================="
	echo ""
	cat /var/log/apache2/access.log
fi

if [ -e /var/log/apache2/error.log ] ; then
	echo ""
	echo "[System] /var/log/apache2/error.log"
	echo "======================================================="
	echo ""
	cat /var/log/apache2/error.log
fi