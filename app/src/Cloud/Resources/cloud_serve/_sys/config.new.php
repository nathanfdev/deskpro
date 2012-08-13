<?php
/*
 *
 * The entire cloud_serve directory should be in the root path of the vhost that serves cloud sites.
 *
 * Add this to the vhost to serve the correct static files:
 * AliasMatch ^/web([0-9]+)/(.*)$ /path/to/builds/$1/web/$2
 *
 */

if (defined('DPC_CONFIG_LOADED')) exit('Cloud config has already been loaded');
define('DPC_CONFIG_LOADED', true);

return array(
	/**
	 * Database details for the lookup database
	 */
	'db_host'     => 'localhost',
	'db_user'     => 'root',
	'db_password' => '',
	'db_name'     => 'members_area',

	/**
	 * Who to email on errors
	 */
	'error_contact' => 'admin@example.com',

	/**
	 * The main cloud website
	 */
	'vendor_url' => 'http://www.deskpro.com/',

	/**
	 * Base path where all current build sources are kept
	 */
	'builds_path' => '/path/to/builds',

	/**
	 * Base path where site data files are written into
	 */
	'datastore_path' => '/path/to/data',
);