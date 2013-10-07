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

namespace DeskPRO\Tests\DbSet;

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

	public function __construct(\Application\DeskPRO\DependencyInjection\DeskproContainer $container)
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
	 * @return int Number of tables dropped
	 */
	private function clearDatabase()
	{
		$this->getDb()->exec("SET FOREIGN_KEY_CHECKS = 0");

		$tables = $this->getDb()->fetchAllCol("SHOW TABLES");
		foreach ($tables as $t) {
			$this->getDb()->exec("DROP TABLE $t");
		}

		$this->getDb()->exec("SET FOREIGN_KEY_CHECKS = 1");

		// Clear the ORM
		$this->getEm()->clear();

		return count($tables);
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
			escapeshellarg(DP_DATABASE_NAME),
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
			escapeshellarg(DP_DATABASE_NAME),
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
	 */
	public function install($force = false)
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
			} catch (\Exception $e) {
				$do_install = true;
			}
		}

		if ($do_install) {
			$this->clearDatabase();
			if ($this->isCached()) {
				$this->installFromCache();
			} else {
				$this->installDeskpro();
				$this->installSet();

				if ($this->cache_dir) {
					$this->dumpToCache();
				}
			}

			$this->getDb()->exec("
				REPLACE INTO `settings` (`name`, `value`)
				VALUES ('core.dp_testing_dbset', '" . $this->getCacheName() . "')
			");
		}
	}


	/**
	 * Installs a fresh DeskPRO database with the bare data to make it a functional install.
	 *
	 * @return int The number of queries executed
	 */
	private function installDeskpro()
	{
		$em = $this->getEm();

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

		#------------------------------
		# Init data
		#------------------------------

		$agent = new \Application\DeskPRO\Entity\Person();
		$agent->first_name = 'Admin';
		$agent->last_name = 'Admin';
		$agent->setEmail('admin@example.com', true);
		$agent->setPassword('password');
		$agent->is_user = true;
		$agent->is_confirmed = true;
		$agent->is_agent_confirmed = true;
		$agent->is_agent = true;
		$agent->can_agent = true;
		$agent->can_admin = true;
		$agent->can_billing = true;
		$agent->can_reports = true;

		$em->persist($agent);
		$em->flush();

		$this->getDb()->insert('permissions', array('person_id' => $agent->id, 'name' => 'admin.use', 'value' => 1));

		// Install data stuff
		$AGENTGROUP_ALL = null; // should be defined by the time we finish processing data.php
		$USERGROUP_EVERYONE = null; // should be defined by the time we finish processing data.php
		$AGENT = $agent; // can be used in data.php
		$WEB_INSTALL = true;
		$IMPORT_INSTALL = false;

		$install_data = new \Application\InstallBundle\Install\InstallDataReader(DP_ROOT.'/src/Application/InstallBundle/Data/data.php');
		$translate = $this->container->get('deskpro.core.translate');

		foreach ($install_data as $php) {
			eval($php);
		}

		$em->flush();

		\Application\DeskPRO\DataSync\AbstractDataSync::syncAllBaseToLive();

		// For the all agent group, fetch permissions from the template
		if ($AGENTGROUP_ALL) {
			$scanner = new \Application\InstallBundle\Data\AgentGroupPermScanner();
			foreach ($scanner->getNames() as $p_name) {
				$p = new \Application\DeskPRO\Entity\Permission();
				$p->usergroup = $AGENTGROUP_ALL;
				$p->name = $p_name;
				$p->value = 1;
				$em->persist($p);
			}
			$em->flush();

			$ch = new \Application\DeskPRO\ORM\CollectionHelper($agent, 'usergroups');
			$ch->setCollection(array($AGENTGROUP_ALL));
			$em->persist($agent);
			$em->flush();
		}

		if ($USERGROUP_EVERYONE) {
			$scanner = new \Application\InstallBundle\Data\UserGroupPermScanner();
			foreach ($scanner->getNames() as $p_name) {
				$p = new \Application\DeskPRO\Entity\Permission();
				$p->usergroup = $USERGROUP_EVERYONE;
				$p->name = $p_name;
				$p->value = 1;
				$em->persist($p);
			}
			$em->flush();
		}

		$data_init = new \Application\InstallBundle\Data\DataInitializer($this->container);
		$data_init->admin_user = $agent;
		$data_init->run();

		// Initial settings that mark as as "installed"
		$this->getDb()->exec("
			REPLACE INTO `settings` (`name`, `value`)
			VALUES
				('core.app_secret', 'YXI5Z2HSQ9IF8KROQQ63GL4FB4CV57ZIIZ7CZO68FUDYBZIP2M'),
				('core.cron_logreport.cli-phperr.log', '1380716762'),
				('core.default_from_email', 'noreply@example.com'),
				('core.default_timezone', 'UTC'),
				('core.deskpro_build', '".time()."'),
				('core.deskpro_build_num', '0'),
				('core.deskpro_url', 'http://localhost:8888/'),
				('core.deskpro_version', '20131002122551'),
				('core.done_data_initializer', '1'),
				('core.done_rewrite_urls_check', '".time()."'),
				('core.install_build', '".time()."'),
				('core.install_key', '6S7X77ZAR2CYSDT4GJCJ'),
				('core.install_timestamp', '".time()."'),
				('core.install_token', 'PUGYIA9E82Z8JCPKO0NKGC957HITHNZRFHY4CQ3V1380214398'),
				('core.last_cron_run', '".time()."'),
				('core.last_cron_start', '".time()."'),
				('core.license', 'TlZNVi0wMTEyLUZVVVNFVEJHVFJNRU9KQlNHVlJNUVNTUgERC3\r\nlkZGRncEQKPwB2IyU+LiJjOgZ9FhE8ARdRIQ4OCR8seUR0ZRUZ\r\nJi9+cQB4eTF5ZjQ3P2J5TXYxdREHWzB/a1xiVQ0KeQdqMS5Qf1\r\nYtWXwZagd5DX9OCxASXzAzNGJmGTE7HhAKEBBnODZiGyYGAXVt\r\nLh8TKxcMQyFbKiAhP08aEFoECSM4TQkmMS8mEXJ1UQQINRcsAG\r\noHPBBxZxcFP1l7Uw8TJwseDn1IXAI5WwxLfVQoASkUClloBy93\r\nUEF2XFMQCwYFSC9aewFYHwJVeV0RAAonCEkhIzkjHn8WWSkRPn\r\ncpVyxrMQw6fARnIk8TDQcQCGcZRSombUhedVMENwhxUmpTLUIV\r\nZHRUflZ5UAhnAVs0CyhTZgspTkUIfQVdNWA'),
				('core.rewrite_urls', '1'),
				('core.setup_initial', '1'),
				('core.task_completed_add_ticketfield', '".time()."'),
				('core.twitter_last_cleanup', '".time()."'),
				('core.use_agent_team', '1'),
				('core_tickets.enable_like_search_auto', '1'),
				('user.kb_subscriptions_last', '".time()."');
		");
		$count++;

		return $count;
	}


	/**
	 * Install data specific to this set.
	 *
	 * @return int
	 */
	abstract protected function installSet();
}