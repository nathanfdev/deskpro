<?php
define('DP_LIC_SERVER', 'http://deskpro.devput/deskpro_tools/index_dev.php');
define('DP_LIC_STR', 'MDAwMC0wMDAwLTAwMDBUR1FHQ1NPSFhMQk9IQ1ZJU0ZLUGlSA3p0aQFic35UP1UpUWcLaRt+ZmNZaDQ6VFR3LwQkNzxNejMmIRsOelxiChREPwEXGikODgkfLHlADRAXCAQ/fj0uIXhqa2J6H1NLfFBnbXcCXyBrFnIMak03fz5JeFg8SSpfKwZxSXpUfwUqCykAP1wwIDhdZg86NxYQCRIAZztRGiQ/PnUubyoYAytbKkMbBjENI2gdDBQFJgkhOFYZDFIwCR5dFn8HHCIpOBx1BjFzHRABUix3blUHLScILx47CWB1LUBmbGoVZDdpHnxYfh11DHtJEkkvRj1gfQx4TC9LfGQoA10AF18eGiVDB3ooJgILEFgMMyk0MlY5MQcyOTYiSQ4ANR0FGBNZMR4pLVYWW28+CgxYfw9/ACxEDmYAU3EcWgABYGldcV59AmFbKh9AXnkFVjZt');
define('DP_INSTALL_KEY', 'DXRPIOOUEFHSRPLIIWPT');

$DP_CONFIG = array();

//require(DP_ROOT . '/dev_debug.php');
//require(DP_ROOT . '/debug.php');

$DP_CONFIG['mail'] = array();
$DP_CONFIG['mail']['default_from'] = 'chris.nadeau@deskpro.com';

$DP_CONFIG['SETTINGS'] = array();
$DP_CONFIG['SETTINGS']['core.site_url'] = 'http://www.deskpro.com/';
$DP_CONFIG['SETTINGS']['core.site_name'] = 'DeskPRO.com';
$DP_CONFIG['SETTINGS']['core.deskpro_name'] = 'Helpdesk';
$DP_CONFIG['SETTINGS']['core.deskpro_url'] = 'http://deskpro.devput/dp_400/';
$DP_CONFIG['SETTINGS']['core.deskpro_url'] = 'http://test-instant.deskprodev.com/';
$DP_CONFIG['SETTINGS']['core.deskpro_url'] = 'http://deskpro.devput/dp_400/';
$DP_CONFIG['SETTINGS']['core.deskpro_assets_full_url'] = 'http://deskpro.devput/dp_400/static/';
$DP_CONFIG['SETTINGS']['core.disqus_shortname'] = 'deskprodev';
$DP_CONFIG['SETTINGS']['core.facebook_like'] = true;
$DP_CONFIG['SETTINGS']['user.disable_chat_element'] = true;
//$DP_CONFIG['SETTINGS']['core.comments_adapter'] = 'disqus';
//$DP_CONFIG['SETTINGS']['core.comments_adapter'] = 'facebook';
//$DP_CONFIG['SETTINGS']['core.facebook_like'] = true;

################################################################################
# Database Configuration
################################################################################
$DP_CONFIG['db'] = array();

/**
 * The database server, usually localhost
 */
$DP_CONFIG['db']['host'] = 'slinky';

/**
 * The database user
 */
$DP_CONFIG['db']['user'] = 'root';

/**
 * The password for the database user
 */
//$DP_CONFIG['db']['password'] = 'root';
$DP_CONFIG['db']['password'] = 'mysquirrel';

/**
 * The name of the database
 */
//$DP_CONFIG['db']['dbname'] = 'dp_400_test';
//$DP_CONFIG['db']['dbname'] = 'dp_400';
$DP_CONFIG['db']['dbname'] = 'dp400_n5';
//$DP_CONFIG['db']['dbname'] = 'dp_400demo';
//$DP_CONFIG['db']['dbname'] = 'newtest001';
//$DP_CONFIG['db']['dbname'] = 'newtest05';
//$DP_CONFIG['db']['dbname'] = 'newst04';
//$DP_CONFIG['db']['dbname'] = 'loadtest01';

/**
 * The database driver. Currently only pdo_mysql is supported.
 */
$DP_CONFIG['db']['driver'] = 'pdo_mysql';


################################################################################
# Search Engine Optione
################################################################################

$DP_CONFIG['search'] = array();

/**
 * MySQL search allows basic fulltext searching, but is slow for larger
 * databases and does not offer similarity search.
 */
//$DP_CONFIG['search']['adapter'] = 'mysql';

/**
 * Elastic search is fast and scalable, and offers all the search capabiltiies
 * supported by DeskPRO.
 */
$DP_CONFIG['search']['adapter'] = 'elastic';
$DP_CONFIG['search']['options'] = array(
	'host' => '127.0.0.1',
	'port' => 9200
);



################################################################################
# Cache Options
################################################################################

$DP_CONFIG['cache'] = array();

/**
 * The common cache includes things like names of categories, settings,
 * and other common object attributes.
 */
//$DP_CONFIG['cache']['common'] = array(
//	'backend' => 'Sqlite',
//	'cache_db_complete_path' => '%kernel.cache_dir%/cache_common.db',
//);

/**
 * The portal cache includes things like category counts
 */
//$DP_CONFIG['cache']['portal'] = array(
//	'backend' => 'Sqlite',
//	'cache_db_complete_path' => '%kernel.cache_dir%/cache_portal.db',
//);

//$DP_CONFIG['doctrine_cache_type'] = 'sqlite';



################################################################################
# Elastica Clients
################################################################################

$DP_CONFIG['elastica'] = array(
	'default_client' => 'default',
	'clients' => array()
);

$DP_CONFIG['elastica']['clients']['default'] = array();
$DP_CONFIG['elastica']['clients']['default']['host'] = '192.168.0.100';
$DP_CONFIG['elastica']['clients']['default']['port'] = 9200;



################################################################################
# Misc
################################################################################

/**
 * Path to serve static files from, with trailing slash.
 * When none provided, the /static/ directory under the current request is used.
 */
//$DP_CONFIG['static_path'] = 'file:///Users/chroder/Sites/deskpro/dp_400/static';

/**
 * Path to store cache files. Defaults to appfiles/sys/cache
 */
//$DP_CONFIG['cache_dir'] = '/home/chroder/dp400_cache/%env%';

$DP_CONFIG['debug'] = array('dev' => true);

// Include debug file (all are disabled by default)
//require(DP_ROOT . '/debug.php');
