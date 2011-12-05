<?php
$DP_CONFIG = array();

################################################################################
# Database Configuration
################################################################################

$DP_CONFIG['db'] = array();

/**
 * The database server, usually localhost
 */
$DP_CONFIG['db']['host'] = 'localhost';

/**
 * The database user
 */
$DP_CONFIG['db']['user'] = 'YOUR_DATABASE_USER';

/**
 * The password for the database user
 */
$DP_CONFIG['db']['password'] = 'YOUR_DATABASE_PASS';

/**
 * The name of the database
 */
$DP_CONFIG['db']['dbname'] = 'YOUR_DATABASE_NAME';

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
$DP_CONFIG['search']['adapter'] = 'mysql';

/**
 * Elastic search is fast and scalable, and offers all the search capabiltiies
 * supported by DeskPRO.
 */
/*
$DP_CONFIG['search']['adapter'] = 'elastic';
$DP_CONFIG['search']['options'] = array(
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
//$DP_CONFIG['cache_common'] = array(
//	'backend' => 'Sqlite',
//	'cache_db_complete_path' => '%kernel.cache_dir%/cache_common.db',
//);


################################################################################
# Elastica Clients
################################################################################

$DP_CONFIG['elastica'] = array(
	'default_client' => 'default',
	'clients' => array()
);

//$DP_CONFIG['elastica']['clients']['default'] = array();
//$DP_CONFIG['elastica']['clients']['default']['host'] = 'localhost';
//$DP_CONFIG['elastica']['clients']['default']['port'] = 9200;


################################################################################
# Misc
################################################################################

/**
 * Path to serve static files from, with trailing slash.
 * When none provided, the /static/ directory under the current request is used.
 */
//$DP_CONFIG['static_path'] = 'http://static.example.com/';
