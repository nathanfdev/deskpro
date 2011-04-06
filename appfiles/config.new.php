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
# Cache Options
################################################################################

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
# Misc
################################################################################

/**
 * Path to store cache files. Defaults to appfiles/sys/cache
 */
//$CONFIG['cache_dir'] = '/some/path';

// Include debug file (all debug options are disabled by default)
require(DP_ROOT . '/debug.php');