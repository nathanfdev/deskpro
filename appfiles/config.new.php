<?php
$CONFIG = array();

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
# Misc
################################################################################

/**
 * Path to store cache files. Defaults to appfiles/sys/cache
 */
//$CONFIG['cache_dir'] = '/some/path';