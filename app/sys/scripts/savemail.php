<?php if (!defined('DP_ROOT')) exit('No access');
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage SystemScripts
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 */

/**
 * This handles a raw email submitted via a PUT request.
 */

require DP_ROOT.'/sys/load_config.php';
dp_load_config();

#------------------------------
# Verify auth
#------------------------------

if (!defined('DP_SAVEMAIL_AUTH')) {
	echo "DP_SAVEMAIL_AUTH is not defined";
	exit(1);
}

if (!isset($_GET['auth']) || $_GET['auth'] != DP_SAVEMAIL_AUTH) {
	echo "Invalid auth code";
	exit(1);
}

if (!isset($_FILES['mailfile']) || !empty($_FILES['mailfile']['error']) || empty($_FILES['mailfile']['tmp_name'])) {
	echo "Invalid mailfile";
	exit(1);
}

#------------------------------
# Verify save directories
#------------------------------

if (!defined('DP_SAVEMAIL_DIR')) {
	define('DP_SAVEMAIL_DIR', dp_get_data_dir() . '/emailstore');
}

if (!is_dir(DP_SAVEMAIL_DIR)) {
	if (!mkdir(DP_SAVEMAIL_DIR, 0755, true)) {
		echo "Could not create DP_SAVEMAIL_DIR directory";
		exit(1);
	}
}

$cat = isset($_GET['cat']) ? $_GET['cat'] : 'default';

if (!preg_match('#^[a-zA-Z0-9\-_][a-zA-Z0-9\-_.@]*$#', $cat)) {
	echo "Invalid cat";
	exit(1);
}

$cat = strtolower($cat);
$cat_dir = DP_SAVEMAIL_DIR . '/' . $cat;

if (!is_dir($cat_dir)) {
	if (!mkdir($cat_dir, 0755, true)) {
		echo "Could not create DP_SAVEMAIL_DIR directory";
		exit(1);
	}
}

#------------------------------
# Save input
#------------------------------

$name = date('Y-m-d.H-i-s') . '-' . mt_rand(100000000,999999999) . '.eml';
move_uploaded_file($_FILES['mailfile']['tmp_name'], $cat_dir . '/' . $name);
chmod($cat_dir . '/' . $name, 0777);

echo "DP_MAIL_ACCEPT";