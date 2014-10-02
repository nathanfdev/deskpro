<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

class DpTestEnv
{
	/**
	 * @var array
	 */
	private static $reset_db_sets = array();

	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private static $last_container = null;

	/**
	 * @var int
	 */
	private static $container_count = 0;

	public static function init()
	{
		static $has_init;
		if ($has_init) return;

		@file_put_contents(DP_WEB_ROOT.'/running_tests.trigger', time());
		register_shutdown_function(function() {
			@unlink(DP_WEB_ROOT.'/running_tests.trigger');
		});
	}


	/**
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	public static function getContainer()
	{
		if (self::$last_container !== null) {
			return self::$last_container;
		}
		$kernel = new \DeskPRO\Kernel\DpKernel('dev', true, 'sys');
		$kernel->boot('cli');

		self::$last_container = $kernel->getContainer();
		self::$container_count++;

		return $kernel->getContainer();
	}


	public static function getMockBuilder($name)
	{

	}


	/**
	 * @return int
	 */
	public static function getCountainerCounter()
	{
		return self::$container_count;
	}


	/**
	 * Resets the container so next time it'll be re-created.
	 */
	public static function resetContainer()
	{
		self::$last_container = null;
	}


	/**
	 * Enables the current database set. $reset will reset the database if it already exists.
	 *
	 * @param string $set_name     The set name to restore
	 * @param bool   $reset        Force reset the database even if it already exists
	 * @param bool   $reset_after  Reset the database after (eg next time it is used). Use this to reset the db after a destructive test.
	 */
	public static function enableDatabaseSet($set_name = 'FreshDb', $reset = false, $reset_after = false)
	{
		self::resetContainer();

		require_once(DP_ROOT . '/testing/data/DpDbSets/AbstractDbSet.php');
		if (file_exists(DP_ROOT . '/testing/data/DpDbSets/'.$set_name.'.php')) {
			require_once(DP_ROOT . '/testing/data/DpDbSets/'.$set_name.'.php');
		}

		$cache_path = DP_ROOT.'/testing/data/dbset-cache';
		if (!is_dir($cache_path)) {
			mkdir($cache_path, 0777, true);
			chmod($cache_path, 0777);
		}

		$set_class = "DpDbSets\\$set_name";
		if (!class_exists($set_class)) {
			throw new \InvalidArgumentException("Invalid set name: $set_name ($set_class)");
		}

		$GLOBALS['DP_TESTING_USEDB'] = 'dp_test_' . strtolower($set_name);

		$set = new $set_class(self::getContainer());
		$set->enableCache(
			$cache_path,
			'mysql',
			'mysqldump'
		);
		$set->install($reset, $reset_after);

		@file_put_contents(DP_WEB_ROOT.'/testing_db_name', $GLOBALS['DP_TESTING_USEDB']);
		register_shutdown_function(function() {
			@unlink(DP_WEB_ROOT.'/testing_db_name');
		});
	}


	/**
	 * This just resets the 'active' db pointer to use
	 * whatever is set in config.testing.php (DP_DATABASE_NAME).
	 * So the next time you fetch the container, you'll get a conn
	 * to the default db.
	 */
	public static function enableDefaultDb()
	{
		unset($GLOBALS['DP_TESTING_USEDB']);
		@unlink(DP_WEB_ROOT.'/testing_db_name');
	}
}