<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DpDbSets;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\ORM\EntityManager;
use Orb\Util\Util;

abstract class AbstractDbSet
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	private $db;

	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var string
	 */
	private $cache_dir;

	/**
	 * @var string
	 */
	private $mysql_bin_path;

	/**
	 * @var string
	 */
	private $mysqldump_bin_path;

	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
		$this->em = $container->getEm();
		$this->db = $this->em->getConnection();
	}


	/**
	 * Disables the cache
	 */
	public function enableCache($cache_dir, $mysql_bin_path = 'mysql', $mysqldump_bin_path = 'mysqldump')
	{
		$this->cache_dir          = $cache_dir;
		$this->mysql_bin_path     = $mysql_bin_path;
		$this->mysqldump_bin_path = $mysqldump_bin_path;
	}


	/**
	 * @return \Application\DeskPRO\DBAL\Connection
	 */
	public function getDb()
	{
		return $this->db;
	}


	/**
	 * @return \Application\DeskPRO\ORM\EntityManager
	 */
	public function getEm()
	{
		return $this->em;
	}


	/**
	 * @return DeskproContainer
	 */
	public function getContainer()
	{
		return $this->container;
	}


	/**
	 * @return int Number of tables dropped
	 */
	private function clearDatabase()
	{
		$this->getDb()->exec("DROP DATABASE {$this->getDatabaseName()}");
		$this->getDb()->exec("CREATE DATABASE {$this->getDatabaseName()}");
		$this->getDb()->exec("USE {$this->getDatabaseName()}");

		// Clear the ORM
		$this->getEm()->clear();

		return 1;
	}


	/**
	 * Get the cache name for this set
	 *
	 * @return string
	 */
	private function getCacheName()
	{
		return Util::getBaseClassname($this);
	}


	/**
	 * Get the cache file path for this set
	 *
	 * @return string
	 */
	private function getCachePath()
	{
		if (!$this->cache_dir) {
			throw new \RuntimeException("No cache directory is set");
		}

		return $this->cache_dir . DIRECTORY_SEPARATOR . $this->getCacheName();
	}


	/**
	 * @return bool
	 */
	private function isCached()
	{
		if ($this->cache_dir && file_exists($this->getCachePath())) {
			return true;
		}

		return false;
	}


	/**
	 * Dumps the database to the cache file
	 */
	private function dumpToCache()
	{
		$cmd = sprintf(
			"%s --opt -Q -h%s --port=%s -u%s -p%s %s > %s",
			$this->mysqldump_bin_path,
			escapeshellarg(DP_DATABASE_HOST),
			escapeshellarg(3306),
			escapeshellarg(DP_DATABASE_USER),
			DP_DATABASE_PASSWORD,
			escapeshellarg($this->getDatabaseName()),
			escapeshellarg($this->getCachePath())
		);

		$cmd .= ' 2>&1';
		$ret = 0;
		exec($cmd, $out, $ret);

		if ($ret) {
			echo "Command Failed: $cmd\n";
			echo implode("\n",$out);
			throw new \RuntimeException();
		}
	}


	/**
	 * Installs the set from the cached SQL
	 *
	 * @return bool
	 */
	private function installFromCache()
	{
		$cmd = sprintf(
			'%s -h%s -u%s -p%s %s < %s',
			$this->mysql_bin_path,
			escapeshellarg(DP_DATABASE_HOST),
			escapeshellarg(DP_DATABASE_USER),
			escapeshellarg(DP_DATABASE_PASSWORD),
			escapeshellarg($this->getDatabaseName()),
			escapeshellarg($this->getCachePath())
		);

		$cmd .= ' 2>&1';
		$ret = 0;
		exec($cmd, $out, $ret);

		if ($ret) {
			echo "Command Failed: $cmd\n";
			echo implode("\n",$out);
			throw new \RuntimeException();
		}
	}


	/**
	 * Installs the db set:
	 * - Clears the current database if its not empty
	 * - Installs a fresh DeskPRO version
	 * - Applies the set that installs any additional data on the database
	 *
	 * @param bool $force   True to force resetting the DB even if the set is already installed (e.g., resetting after every test)
	 * @param bool $reset_after  Reset the database after (eg next time it is used). Use this to reset the db after a destructive test.
	 */
	public function install($force = false, $reset_after = false)
	{
		$do_install = false;

		if ($force) {
			$do_install = true;
		} else {
			try {
				$installed_set = $this->getDb()->fetchColumn("SELECT value FROM settings WHERE name = 'core.dp_testing_dbset'");
				if (!$installed_set || $installed_set != $this->getCacheName()) {
					$do_install = true;
				}

				$is_marked_reset = $this->getDb()->fetchColumn("SELECT value FROM settings WHERE name = 'core.dp_testing_dbset_resetafter'");
				if ($is_marked_reset) {
					$do_install = true;
				}
			} catch (\Exception $e) {
				$do_install = true;
			}
		}

		if ($do_install) {
			$this->clearDatabase();
			if ($this->isCached()) {
				$this->installFromCache();
			} else {
				$this->installDatabase();
				$this->installSet();

				if ($this->cache_dir) {
					$this->dumpToCache();
				}
			}

			$this->getDb()->exec("
				REPLACE INTO `settings` (`name`, `value`)
				VALUES ('core.dp_testing_dbset', '" . $this->getCacheName() . "')
			");

			if ($reset_after) {
				$this->getDb()->exec("
					REPLACE INTO `settings` (`name`, `value`)
					VALUES ('core.dp_testing_dbset_resetafter', '1')
				");
			}
		}
	}


	/**
	 * Installs a fresh DeskPRO database with the bare data to make it a functional install.
	 *
	 * @return int The number of queries executed
	 */
	private function installDatabase()
	{
		$base_schema_cache = null;
		if ($this->cache_dir) {
			$base_schema_cache = $this->cache_dir . '/base_schema.php';
		}

		if ($base_schema_cache && file_exists($base_schema_cache)) {
			$queries = require($base_schema_cache);
		} else {
			$gs = new \Application\InstallBundle\Data\GenerateSchema($this->getEm());
			$queries = array(
				'creates' => $gs->getCreates(),
				'alters'  => $gs->getAlters()
			);

			if ($base_schema_cache) {
				file_put_contents(
					$base_schema_cache,
					"<?php return " . var_export($queries, true) . ";\n"
				);
			}
		}

		$count = 1;

		// Manually create install_data
		// Its used by the installer to test that we have create perms, so its
		// not part of the schema
		$this->getDb()->exec("
			CREATE TABLE `install_data` (
			  `build` varchar(30) NOT NULL,
			  `name` varchar(75) NOT NULL DEFAULT '',
			  `data` blob NOT NULL,
			  PRIMARY KEY (`build`,`name`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		");
		foreach ($queries['creates'] as $q) {
			$count++;
			$this->getDb()->exec($q);
		}

		foreach ($queries['alters'] as $q) {
			$count++;
			$this->getDb()->exec($q);
		}

		return $count;
	}


	/**
	 * @return string
	 */
	private function getDatabaseName()
	{
		$db_name = DP_DATABASE_NAME;
		if (isset($GLOBALS['DP_TESTING_USEDB'])) {
			$db_name = $GLOBALS['DP_TESTING_USEDB'];
		}

		return $db_name;
	}


	/**
	 * Install data specific to this set.
	 *
	 * @return int
	 */
	abstract protected function installSet();
}