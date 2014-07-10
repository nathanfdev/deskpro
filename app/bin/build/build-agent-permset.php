#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
	echo "This script must only be run from the CLI.\n";
	echo "Contact support@deskpro.com if you require assistance.\n";
	exit(1);
}

chdir(__DIR__);

define('DP_BUILDING', true);
define('DP_ROOT', realpath(__DIR__ . '/../../'));
define('DP_WEB_ROOT', realpath(__DIR__ . '/../../../'));
define('DP_CONFIG_FILE', DP_WEB_ROOT . '/config.php');

require DP_ROOT . '/bin/build/inc.php';
require_once DP_ROOT . '/sys/load_config.php';
require DP_ROOT . '/bin/build/php-path.php';

$scanner = new \Application\InstallBundle\Data\AgentGroupPermScanner();
$perm_names = array(
	'all'  => $scanner->getNames(),
	'safe' => $scanner->getSafeNames()
);

$write_path = DP_ROOT . '/sys/Resources/agent-perm-names.php';
$data = var_export($perm_names, true);

file_put_contents($write_path, '<?php return ' . $data . ";\n");