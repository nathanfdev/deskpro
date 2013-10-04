<?php
define('DP_BOOT_MODE', 'testing');
require(realpath(__DIR__. '/../../index.php'));
require(__DIR__ . '/ContainerTestCase.php');
require(__DIR__ . '/DatabaseTestCase.php');

@file_put_contents(__DIR__.'/../../running_tests.trigger', time());
register_shutdown_function(function() {
	unlink(__DIR__.'/../../running_tests.trigger');
});

class DpTestConfig
{
	/**
	 * @var \DeskPRO\Kernel\CliKernel
	 */
	private static $kernel;

	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private static $container;


	/**
	 * Shut-down the kernel and unset container. Next call to getContainer will be a new one.
	 */
	public static function resetContainer()
	{
		if (self::$kernel) {
			self::$kernel->shutdown();
			self::$kernel = null;
		}
		if (self::$container) {
			self::$container = null;
		}
	}


	/**
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	public static function getContainer()
	{
		if (self::$container) {
			return self::$container;
		}

		$kernel = new \DeskPRO\Kernel\CliKernel('dev', true);
		$kernel->boot('cli');
		self::$container = $kernel->getContainer();
		\Application\DeskPRO\App::setContainer(self::$container, 'default', true);

		return self::$container;
	}


	/**
	 * @return \DeskPRO\Kernel\CliKernel
	 */
	public static function getKernel()
	{
		self::getContainer();
		return self::$kernel;
	}


	/**
	 * Resets the test database
	 */
	public static function initTestDb($verbose = false)
	{
		$em = self::getContainer()->getEm();
		$db = self::getContainer()->getDb();
		$db->exec("SET FOREIGN_KEY_CHECKS = 0");

		#------------------------------
		# Clear database
		#------------------------------

		if ($verbose) printf("\nClearing database ...\n");
		$t_start = microtime(true);

		foreach ($db->fetchAllCol("SHOW TABLES") as $t) {
			$db->exec("DROP TABLE $t");
			if ($verbose) echo ".";
		}
		if ($verbose) printf("\nDone in %.4fs", microtime(true)-$t_start);


		#------------------------------
		# Create tables
		#------------------------------

		if ($verbose) printf("Creating tables ...\n");
		$t_start = microtime(true);

		$gs = new \Application\InstallBundle\Data\GenerateSchema(DpTestConfig::getContainer()->getEm());

		if ($verbose) printf("Creates -- ");

		// Manually create install_data
		// Its used by the installer to test that we have create perms, so its
		// not part of the schema
		$db->exec("
			CREATE TABLE `install_data` (
			  `build` varchar(30) NOT NULL,
			  `name` varchar(75) NOT NULL DEFAULT '',
			  `data` blob NOT NULL,
			  PRIMARY KEY (`build`,`name`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		");
		foreach ($gs->getCreates() as $q) {
			$db->exec($q);
		}
		if ($verbose) printf("Done\n");

		if ($verbose) printf("Alters -- ");
		foreach ($gs->getAlters() as $q) {
			$db->exec($q);
		}
		if ($verbose) printf("Done\n");
		if ($verbose) printf("\nDone in %.4fs", microtime(true)-$t_start);

		$db->exec("SET FOREIGN_KEY_CHECKS = 1");

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

		$db->insert('permissions', array('person_id' => $agent->id, 'name' => 'admin.use', 'value' => 1));

		// Install data stuff
		$AGENTGROUP_ALL = null; // should be defined by the time we finish processing data.php
		$USERGROUP_EVERYONE = null; // should be defined by the time we finish processing data.php
		$AGENT = $agent; // can be used in data.php
		$WEB_INSTALL = true;
		$IMPORT_INSTALL = false;

		$install_data = new \Application\InstallBundle\Install\InstallDataReader(DP_ROOT.'/src/Application/InstallBundle/Data/data.php');
		$translate = self::getContainer()->get('deskpro.core.translate');

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

		$data_init = new \Application\InstallBundle\Data\DataInitializer(self::getContainer());
		$data_init->admin_user = $agent;
		$data_init->run();

		#------------------------------
		# Init settings
		#------------------------------

		$db->exec("
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

		#------------------------------
		# Reset container
		#------------------------------

		self::resetContainer();
	}
}