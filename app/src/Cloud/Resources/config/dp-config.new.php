<?php
exit; // remove when copied to real location

/**
 * This is a sampel configuration file to put in the root (/config.php) that loads
 * a DeskPRO instance in cloud mode.
 */

$DP_CONFIG = array();
$DP_CONFIG['debug'] = array();

require DP_ROOT.'/src/Cloud/CloudConfig.php';
\Cloud\CloudConfig::loadFromWeb();