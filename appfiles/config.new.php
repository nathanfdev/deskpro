<?php
$CONFIG = array();

$CONFIG['mail'] = array();
$CONFIG['mail']['default_from'] = 'example@example.com';

################################################################################
# Database Configuration
################################################################################
$CONFIG['db'] = array();

/**
 * The database server, usually localhost
 */
$CONFIG['db']['host'] = 'localhost';

/**
 * The database user
 */
$CONFIG['db']['user'] = 'root';

/**
 * The password for the database user
 */
$CONFIG['db']['password'] = '';

/**
 * The name of the database
 */
$CONFIG['db']['dbname'] = 'deskpro';

/**
 * The database driver. Currently only pdo_mysql is supported.
 */
$CONFIG['db']['driver'] = 'pdo_mysql';


################################################################################
# Search Engine Optione
################################################################################

$CONFIG['search'] = array();

/**
 * MySQL search allows basic fulltext searching, but is slow for larger
 * databases and does not offer similarity search.
 */
$CONFIG['search']['adapter'] = 'mysql';

/**
 * Elastic search is fast and scalable, and offers all the search capabiltiies
 * supported by DeskPRO.
 */
/*
$CONFIG['search']['adapter'] = 'elastic';
$CONFIG['search']['options'] = array(
	'host' => 'localhost',
	'port' => 9200
);
*/

################################################################################
# Cache Options
################################################################################

/**
 * The common cache includes things like names of categories, settings,
 * and other common object attributes.
 */
//$CONFIG['cache_common'] = array(
//	'backend' => 'Sqlite',
//	'cache_db_complete_path' => '%kernel.cache_dir%/cache_common.db',
//);
//
//$CONFIG['doctrine_cache_type'] = 'sqlite';



################################################################################
# Elastica Clients
################################################################################

$CONFIG['elastica'] = array(
	'default_client' => 'default',
	'clients' => array()
);

//$CONFIG['elastica']['clients']['default'] = array();
//$CONFIG['elastica']['clients']['default']['host'] = 'localhost';
//$CONFIG['elastica']['clients']['default']['port'] = 9200;



################################################################################
# Misc
################################################################################

/**
 * Path to serve static files from, with trailing slash.
 * When none provided, the /static/ directory under the current request is used.
 */
//$CONFIG['static_path'] = 'http://static.example.com/';

/**
 * Path to store cache files. Defaults to appfiles/sys/cache
 */
//$CONFIG['cache_dir'] = '/some/path';

// Include debug file (all debug options are disabled by default)
require(DP_ROOT . '/debug.php');