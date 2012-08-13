<?php
// This file should be placed at /config.php for the actual DeskPRO distribution.
// It ensures the correct site is loaded up when accessed indirectly (eg. sub-commands).

define('DPC_SERVE_DIR', '/path/to/cloud_serve');
require(DPC_SERVE_DIR.'/_sys/CloudConfig.php');
if (php_sapi_name() == 'cli') {
	\Cloud\CloudConfig::loadFromCli();
} else {
	\Cloud\CloudConfig::loadFromWeb();
}
require(DPC_SERVE_DIR.'/_sys/dp-config.php');