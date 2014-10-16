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

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Commands
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Monolog\Handler\OrbLoggerAdapterHandler;
use Application\InstallBundle\Data\DefaultDataProcessor;
use Doctrine\DBAL\DBALException;
use Monolog\Logger;
use Orb\Util\Strings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:install');
		$this->addOption('insert-initial', null, InputOption::VALUE_NONE, "Unused (exists for legacy)");
		$this->addOption('admin-email', null, InputOption::VALUE_REQUIRED, "The initial admin email");
		$this->addOption('admin-password', null, InputOption::VALUE_REQUIRED, "The initial admin password");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->ensureNotInstalled()) {
            exit;
        }

		if (!$input->getOption('admin-email') || !$input->getOption('admin-password')) {
			echo "Please specify --admin-email and --admin-password\n";
			return 1;
		}

        $this->createDatabase();
        $this->getLogger()->log('Install::createTables', 'debug');

        $db = $this->getDb();
        $check = $db->fetchColumn("SHOW TABLES LIKE 'install_data'");

        if ($check != 'install_data') {
            try {
                $db->exec("
					CREATE TABLE `install_data` (
					  `build` varchar(30) NOT NULL,
					  `name` varchar(75) NOT NULL DEFAULT '',
					  `data` blob NOT NULL,
					  PRIMARY KEY (`build`,`name`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1
				");
            } catch (\Exception $e) {
                $this->getLogger()->log('Failed to craete install_data: ' . $e->getCode() . ' ' . $e->getMessage(), 'err');
                return;
            }

            $tableinfo = $db->fetchColumn("SHOW CREATE TABLE `install_data`", array(), 1);
            if (stripos($tableinfo, 'innodb') === false) {
                $this->getLogger()->log('install_data is not innodb', 'err');
                return;
            }
        }

        if (!defined('DP_BUILD_TIME')) {
            $build_file = DP_ROOT.'/sys/config/build-time.php';
            if (is_file($build_file)) {
                require $build_file;
            } else {
                define('DP_BUILD_TIME', time());
            }
        }

        $logger = new \Orb\Log\Logger();
        $schema = null;
        if (file_exists(DP_ROOT.'/src/Application/InstallBundle/Data/schema.php')) {
            $schema = require DP_ROOT.'/src/Application/InstallBundle/Data/schema.php';
        } else {
            $logger->log('schema.php does not exist, will auto-generate', 'debug');
        }

        $install_schema = new \Application\InstallBundle\Install\InstallSchema($this->getDb(), $schema, DP_BUILD_TIME);

        $install_schema->setLogger($logger);

        $install_schema->run(false);

		#------------------------------
		# Install Data
		#------------------------------

		$initial_password = 'password';
		$initial_email    = 'admin@example.com';

		if ($input->getOption('admin-email')) {
			$initial_email = $input->getOption('admin-email');
		}
		if ($input->getOption('admin-password')) {
			$initial_password = $input->getOption('admin-password');
		}

		if ($initial_email == 'CONFIG') {
			if (defined('DP_TECHNICAL_EMAIL')) {
				$initial_email = DP_TECHNICAL_EMAIL;
			} else {
				$initial_email = 'admin@example.com';
			}
		}

		$agent = new \Application\DeskPRO\Entity\Person();
		$agent->first_name = 'Admin';
		$agent->last_name = 'Admin';
		$agent->setEmail($initial_email, true);
		$agent->setPassword($initial_password);
		$agent->is_user = true;
		$agent->is_confirmed = true;
		$agent->is_agent_confirmed = true;
		$agent->is_agent = true;
		$agent->can_agent = true;
		$agent->can_admin = true;
		$agent->can_billing = true;
		$agent->can_reports = true;

		$this->getOrm()->persist($agent);
		$this->getOrm()->flush();

		$this->getDb()->insert('permissions', array('person_id' => $agent->id, 'name' => 'admin.use', 'value' => 1));

		// Install data stuff
		$AGENTGROUP_ALL = null; // should be defined by the time we finish processing data.php
		$USERGROUP_EVERYONE = null; // should be defined by the time we finish processing data.php
		$AGENT = $agent; // can be used in data.php
		$WEB_INSTALL = true;
		$IMPORT_INSTALL = false;

		$install_data = new \Application\InstallBundle\Install\InstallDataReader(DP_ROOT.'/src/Application/InstallBundle/Data/data.php');
		$em = $this->getOrm();
		$translate = $this->getContainer()->get('deskpro.core.translate');

		foreach ($install_data as $php) {
			eval($php);
		}

		$data_proc = new DefaultDataProcessor($this->getContainer());
		if ($logger) {
			$orb_logger_adapter = new OrbLoggerAdapterHandler($logger);
			$data_proc->setLogger(new Logger('data_proc', array($orb_logger_adapter)));
		}
		$data_proc->runInstall();

		$this->getOrm()->flush();

		\Application\DeskPRO\DataSync\AbstractDataSync::syncAllBaseToLive();

		// For the all agent group, fetch permissions from the template
		if ($AGENTGROUP_ALL) {
			$ch = new \Application\DeskPRO\ORM\CollectionHelper($agent, 'usergroups');
			$ch->setCollection(array($AGENTGROUP_ALL));
			$this->getOrm()->persist($agent);
			$this->getOrm()->flush();
		}

		if ($USERGROUP_EVERYONE) {
			$scanner = new \Application\InstallBundle\Data\UserGroupPermScanner();
			foreach ($scanner->getNames() as $p_name) {
				$p = new \Application\DeskPRO\Entity\Permission();
				$p->usergroup = $USERGROUP_EVERYONE;
				$p->name = $p_name;
				$p->value = 1;
				$this->getOrm()->persist($p);
			}
			$this->getOrm()->flush();
		}

		$data_init = new \Application\InstallBundle\Data\DataInitializer($this->getContainer());
		$data_init->admin_user = $agent;
		$data_init->run();
		App::getDb()->replace('settings', array(
			'name' => 'core.done_data_initializer',
			'value' => 1,
		));

		$app_syncer = new \Application\DeskPRO\App\Native\NativeAppsSync(
			$this->getContainer(),
			$this->getContainer()->getAppManager(),
			new \Application\DeskPRO\App\Package\PackageInstaller($this->getContainer()->getEm(), $this->getContainer()->getBlobStorage(), $this->getContainer()->getImagine()),
			null
		);
		$app_syncer->runSync();

		$this->getContainer()->resetSystemService('app_manager');
		$instance_installer = new \Application\DeskPRO\App\InstanceInstaller(
			$this->getContainer()->getAppManager(),
			$this->getContainer()->getAppManager()->getPackage('deskpro_gravatar'),
			$this->getContainer()->getEm()
		);
		$instance_installer->install('', array(), $this->getContainer());

		App::getDb()->replace('install_data', array(
			'build' => 'default',
			'name' => 'install_build',
			'data' => DP_BUILD_TIME
		));

		App::getDb()->replace('settings', array(
			'name' => 'core.deskpro_build',
			'value' => defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 0,
		));
		App::getDb()->replace('settings', array(
			'name' => 'core.deskpro_build_num',
			'value' => defined('DP_BUILD_NUM') ? DP_BUILD_NUM : 0,
		));
		App::getDb()->replace('settings', array(
			'name' => 'core.install_build',
			'value' => defined('DP_BUILD_TIME') ? DP_BUILD_TIME : time(),
		));

		App::getDb()->replace('settings', array(
			'name' => 'core.install_timestamp',
			'value' => time(),
		));
		App::getDb()->replace('settings', array(
			'name' => 'core.install_key',
			'value' => Strings::random(20, Strings::CHARS_KEY),
		));
		App::getDb()->replace('settings', array(
			'name' => 'core.deskpro_version',
			'value' => date('YmdHis'),
		));
		App::getDb()->replace('settings', array(
			'name' => 'core.install_via_cmd',
			'value' => 1,
		));
    }

    private function createDatabase()
    {
        try {
            App::getDb()->connect();
        } catch (\Exception $e) {
			if ($e instanceof DBALException || $e instanceof \PDOException) {
				if ($e->getCode() == '1049') {

					// Attempt to create an empty database
					try {
						global $DP_CONFIG;
						$dbh = new \PDO("mysql:host={$DP_CONFIG['db']['host']}", $DP_CONFIG['db']['user'], $DP_CONFIG['db']['password']);
						$dbh->exec("CREATE DATABASE `{$DP_CONFIG['db']['dbname']}`");
					} catch (\Exception $e) {
					}
				}
			} else {
				throw $e;
			}
        }
    }

    public function ensureNotInstalled()
    {
        try {
            $this->getDb()->connect();

            $installed = $this->getDb()->fetchColumn("SELECT value FROM settings WHERE name = ?", array('core.install_timestamp'));
            if ($installed) {
                return false;
            }

        } catch (\Exception $e) {
            return true;
        }

        return true;
    }

    public function getDb()
    {
        return App::getDb();
    }

    public function getLogger()
    {
        return new \Orb\Log\Logger();
    }

    public function getOrm()
    {
        return App::getOrm();
    }
}