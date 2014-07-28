#!/bin/bash

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo ""
echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
echo "Error Detected - Here are some interesting log files"
echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"

TEST_LOGS=$SCRIPT_DIR/../logs/*
for f in $TEST_LOGS ; do
	if [[ $f =~ \.png$ ]] ; then
		echo ""
		echo "[Test Screenshot] $f"

		response=$(curl -F "key=b3625162d3418ac51a9ee805b1840452" -H "Expect: " -F "image=@$f" http://imgur.com/api/upload.xml 2>/dev/null)
		if [ $? -ne 0 ]; then
			echo "Upload failed"
		elif [ $(echo $response | grep -c "<error_msg>") -gt 0 ]; then
			echo "Error message from imgur:"
			echo $response | sed -r 's/.*<error_msg>(.*)<\/error_msg>.*/\1/'
		else
			url=$(echo $response | sed -r 's/.*<original_image>(.*)<\/original_image>.*/\1/')
        	echo $url
		fi
	fi
done

echo ""

#for f in $TEST_LOGS ; do
#	if [[ $f =~ \.png$ ]] || [[ $f =~ coverage\.serialized$ ]] ; then
#		true
#	else
#		echo ""
#		echo "[Test Log] $f"
#		echo "======================================================="
#		echo ""
#		if [ -d $f ] ; then
#			cat $f/*
#		else
#			cat $f
#		fi
#	fi
#done

echo ""
echo "tmp output"
echo "======================================================="
echo ""
cat /tmp/output


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
	sudo cat /var/log/apache2/access.log
fi

if [ -e /var/log/apache2/error.log ] ; then
	echo ""
	echo "[System] /var/log/apache2/error.log"
	echo "======================================================="
	echo ""
	sudo cat /var/log/apache2/error.log
fi