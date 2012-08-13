<?php
if (!defined('DP_ROOT')) exit('No access');
if (defined('DPC_CONFIG_LOADED')) exit('Cloud config has already been loaded');
define('DPC_CONFIG_LOADED', true);

return array(
	'db_host'     => 'localhost',
	'db_user'     => 'root',
	'db_password' => '',
	'db_name'     => '',

	'vendor_url' => 'http://www.deskpro.com/',
);