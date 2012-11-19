<?php

ini_set('display_errors', true);

require(__DIR__.'/_sys/CloudConfig.php');
if (php_sapi_name() == 'cli') {
	\Cloud\CloudConfig::loadFromCli();
} else {
	\Cloud\CloudConfig::loadFromWeb();
}

require(DP_WEB_ROOT.'/get_messages.php');
