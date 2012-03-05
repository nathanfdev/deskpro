<?php
/**
 * If you have moved app/ from its default location then you need to update
 * this to the path of DeskPRO's index.php file
 */
define('DP_CMD_LOADER_PATH', realpath(dirname(__FILE__) . '/../index.php'));


#########################################################################################################
# You should not change anything below this line
#########################################################################################################

if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

if (is_file(DP_CMD_LOADER_PATH)) {
	define('DP_BOOT_MODE', 'cli');
	require(DP_CMD_LOADER_PATH);
} else {
	echo "You have moved the DeskPRO /app directory from its default location and it cannot find the index.php loader file.\n";
	echo "You need to edit the following file with the path to DeskPRO's index.php file:\n\n";
	echo "\t" . __FILE__ . "\n\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(2);
}
