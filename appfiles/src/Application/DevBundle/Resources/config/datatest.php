<?php return array(

	/**
	 * A superuser who can create and drop databases, create tables etc.
	 */
	'@superuser' => array(
		// These values if set will override those defined in config.php
		//'user' => 'root',
		//'password' => ''
	),

	/**
	 * The default database that we'll fill with test data.
	 */
	'default' => array(
		'dbname' => 'dp4_test_default',
	),	
);