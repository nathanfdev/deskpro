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

/**
 * Phrase caching caches entire phrase groups, meaning neither the database
 * nor even the filesystem is touched when fething phrases.
 */
//$CONFIG['cache_phrases'] = array(
//	'backend' => 'File',
//	'cache_dir' => '%kernel.cache_dir%/cache_phrases',
//	'file_locking' => true,
//	'read_control' => true,
//	'read_control_type' => 'strlen',
//	'hashed_directory_level' => 0,
//	'cache_file_umask' => 0744,
//);


################################################################################
# Misc
################################################################################

/**
 * Path to store cache files. Defaults to appfiles/sys/cache
 */
//$CONFIG['cache_dir'] = '/some/path';