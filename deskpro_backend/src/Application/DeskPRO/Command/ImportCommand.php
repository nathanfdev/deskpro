<?php

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Log\Logger;

use Orb\Util\Util;
use Orb\Util\Numbers;

class ImportCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	/**
	 * @var \Application\DeskPRO\Log\Logger
	 */
	protected $logger;

	protected function configure()
	{
		$this->setName('dp:import');
		$this->addOption('info', null, InputOption::VALUE_NONE, 'Show information about the importer and config');
		$this->addOption('run', null, InputOption::VALUE_NONE, 'Run the importer from start to finish');
		$this->addOption('step', null, InputOption::VALUE_REQUIRED, 'With --run, Start from this step');
		$this->addOption('exec-step', null, InputOption::VALUE_REQUIRED, 'Execute only this step');
		$this->addOption('exec-step-page', null, InputOption::VALUE_REQUIRED, 'With --exec-step, runs a page of the step. If not specified, page 1 is run.');
		$this->setHelp("This imports data from another platform into the currently installed helpdesk. Please read http://support.deskpro.com/ for more information.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		#----------------------------------------
		# Set environment
		#----------------------------------------

		error_reporting(E_ALL | E_STRICT);
		@ini_set('display_errors', true);
		@ini_set('memory_limit', -1);
		@set_time_limit(0);

		$logger = new Logger();
		$wr = new \Orb\Log\Writer\ConsoleOutputWriter($output);
		$wr->addFilter(new \Orb\Log\Filter\PriorityFilter(Logger::INFO));
		$logger->addWriter($wr);

		$log_file_path = App::getKernel()->getLogDir() . '/import.log';
		try {
			$wr = new \Orb\Log\Writer\Stream($log_file_path);
			$logger->addWriter($wr);
		} catch (\Exception $e) {
			$output->writeln("<error>Log file not writable: $log_file_path</error>");
			$output->writeln("Make the data_logs directory writable and try again.");
			return 1;
		}

		// Override default error logger so it logs to the file
		$GLOBALS['DP_ERR_LOGGER'] = $logger;
		$this->logger = $logger;

		#----------------------------------------
		# Check for database
		#----------------------------------------

		/** @var $db \Application\DeskPRO\DBAL\Connection */
		$db = $this->getContainer()->getDb();
		$DP_CONFIG = $this->getContainer()->getSysConfig('*');

		try {
			$db->connect();
		} catch (\PDOException $e) {
			if ($e->getCode() == '1049') {

				$logger->log("We have detected that the database {$DP_CONFIG['db']['dbname']} does not exist. We will try to create it now ...\n", Logger::INFO);

				// Attempt to create an empty database
				try {
					$dbh = new \PDO("mysql:host={$DP_CONFIG['db']['host']}", $DP_CONFIG['db']['user'], $DP_CONFIG['db']['password']);
					$dbh->exec("CREATE DATABASE `{$DP_CONFIG['db']['dbname']}`");
					$success = true;
				} catch (\Exception $e) {
					$success = false;
				}

				if (!$success) {
					$logger->log('<error>The database name you have set in config.php does not exist and we could not create it.</error>'  . PHP_EOL, Logger::ERR);
					return 21;
				} else {
					$logger->log('The database was created successfully.', Logger::INFO);
				}
			} elseif ($e->getCode() == '1044' || $e->getCode() == '1045') {
				$logger->writeln('<error>The database name you have set in config.php does not exist</error>'  . PHP_EOL);
				return 21;
			} else {
				$logger->log('<error>There was a problem while trying to connect to your database: ' . $e->getMessage() . '</error>'  . PHP_EOL, Logger::ERR);
				return 21;
			}

			$db->connect();
		}

		#----------------------------------------
		# Figure out our run mode
		#----------------------------------------

		$mode = null;
		if ($input->getOption('exec-step') !== null) $mode = 'exec-step';
		elseif ($input->getOption('info')) $mode = 'info';
		elseif ($input->getOption('run')) $mode = 'run';

		$page = 0;
		if ($input->getOption('exec-step-page') !== null) $page = (int)$input->getOption('exec-step-page');
		if (!$page) {
			$page = 1;
		}

		if (!$mode) {
			$logger->log("<error>Choose one of the run modes: --info, --run, --step or --exec-step</error>\n", Logger::INFO);
			return 1;
		}

		#----------------------------------------
		# Get importer config
		#----------------------------------------

		$config = App::getConfig('import');
		if (!$config) {
			$logger->log("<error>There is no `import` configuration.</error>\n", Logger::ERR);
			return 1;
		}

		if (!isset($config['importer'])) {
			$config['importer'] = 'Deskpro3';
		}

		$importer_class = 'Application\\DeskPRO\\Import\\Importer\\' . $config['importer'] . 'Importer';
		if (!class_exists($importer_class)) {
			$logger->log("<error>The `import.importer` class of {$config['importer']} does not exist.</error>\n", Logger::ERR);
			return 3;
		}

		if (isset($config['store_attachment_files'])) {
			global $DP_CONFIG;
			$DP_CONFIG['core.filestorage_method'] = 'fs';
		}

		#----------------------------------------
		# Figure out PHP path
		#----------------------------------------

		$php_path = $this->getContainer()->getPhpBinaryPath();

		if (!$php_path) {
			$logger->log("<error>Unknow path to PHP executable. Edit your /config.php file and specify a value for php_path.</error>\n", Logger::ERR);
			return 1;
		}

		#----------------------------------------
		# Execute an install
		#----------------------------------------

		/** @var $sm \Doctrine\DBAL\Schema\AbstractSchemaManager */
		$sm = $db->getSchemaManager();

		$tables = $sm->listTableNames();
		if ($tables && (!in_array('agent_access', $tables) || !in_array('worker_jobs', $tables))) {
			$logger->log('<error>Your database contains tables but they do not appear to be DeskPRO v4 tables. DeskPRO requires a new, empty database.</error>' . PHP_EOL, Logger::ERR);
			$logger->log('Create a new empty database and edit /config.php with the new details, then try again.'  . PHP_EOL, Logger::ERR);
			return 22;
		}

		if (!$tables) {
			$logger->log('The database you specified is empty. We will now install the DeskPRO v4 tables. This may take a minute.'  . PHP_EOL, Logger::INFO);

			$db->exec("
				CREATE TABLE IF NOT EXISTS `install_data` (
				  `build` varchar(30) NOT NULL,
				  `name` varchar(75) NOT NULL DEFAULT '',
				  `data` blob NOT NULL,
				  PRIMARY KEY (`build`,`name`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1
			");

			if (!defined('DP_BUILD_TIME')) {
				$build_file = DP_ROOT.'/sys/config/build-time.php';
				if (is_file($build_file)) {
					require $build_file;
				} else {
					define('DP_BUILD_TIME', time());
				}
			}

			$schema = null;
			if (file_exists(DP_ROOT.'/src/Application/InstallBundle/Data/schema.php')) {
				$schema = require DP_ROOT.'/src/Application/InstallBundle/Data/schema.php';
			}
			$install_schema = new \Application\InstallBundle\Install\InstallSchema($db, $schema, DP_BUILD_TIME);
			$install_schema->setLogger($logger);

			$errors = array();

			$fn = function($section, $status, $sql, $x, $e = null) use (&$errors) {
				if ($status == 'error') {
					echo '!';
					$errors[] = $e;
				} else {
					echo '.';
				}
			};

			$install_schema->run(false, 100000000, 0, $fn);

			if ($errors) {
				$logger->log("<error>There were errors while trying to install the database: " . implode("\n", $errors) . "</error>\n", Logger::ERR);
				$logger->log("Re-create the database and try again.\n", Logger::INFO);
				return 23;
			}

			// Install default data
			$AGENTGROUP_ALL = null; // should be defiend by the time we finish processing data.php
			$WEB_INSTALL = false;
			$IMPORT_INSTALL = true;

			$this->getContainer()->getEm()->beginTransaction();

			try {
				$install_data = new \Application\InstallBundle\Install\InstallDataReader(DP_ROOT.'/src/Application/InstallBundle/Data/data.php');
				$em = $this->getContainer()->getEm();
				foreach ($install_data as $php) {
					eval($php);
				}

				$this->getContainer()->getEm()->flush();

				// For the all agent group, fetch permissions from the template
				if ($AGENTGROUP_ALL) {
					$scanner = new \Application\InstallBundle\Data\AgentGroupPermScanner();
					foreach ($scanner->getNames() as $p_name) {
						$p = new \Application\DeskPRO\Entity\Permission();
						$p->usergroup = $AGENTGROUP_ALL;
						$p->name = $p_name;
						$p->value = 1;
						$this->getContainer()->getEm()->persist($p);
					}
					$this->getContainer()->getEm()->flush();
				}

				$this->getContainer()->getEm()->getConnection()->commit();
			} catch (\Exception $e) {
				$this->getContainer()->getEm()->getConnection()->rollback();
				throw $e;
			}
		}

		#----------------------------------------
		# Execute DP3 upgrade
		#----------------------------------------

		/** @var $importer \Application\DeskPRO\Import\Importer\Deskpro3Importer */
		$config['log_dir'] = App::getKernel()->getLogDir();
		$config['enable_query_log'] = false;
		$importer = new $importer_class($this->getContainer(), $config, $logger);
		$importer->validateOptions();

		if ($mode == 'run') {
			if ($importer instanceof \Application\DeskPRO\Import\Importer\Deskpro3Importer) {
				$other_version = $importer->getOldDb()->fetchColumn("SELECT value FROM settings WHERE name = ?", array('deskpro_version_internal'));
				if ($other_version < 3050503) {
					$output->writeln('Your DeskPRO v3 installation is outdated. Before we can import your helpdesk into the system, you must run the upgrader.');
					$output->writeln('Do you want to upgrade your v3 database now? A backup will be generated to the /data_backups directory first.');
					$yes = $this->getHelper('dialog')->askConfirmation($output, '[y/N]> ', false);
					if (!$yes) {
						$output->writeln('<error>Aborting. You can re-run this command when you are ready to proceed.</error>');
						return 24;
					}

					#------------------------------
					# Backup
					#------------------------------

					$mysqldump_path = $this->getContainer()->getMysqldumpBinaryPath();
					if (!$mysqldump_path) {
						$output->writeln('We could not locate the path to the MySQL backup utility "mysqldump". You can edit /config.php to specify this path in the "mysqldump_path" setting.');
						return 25;
					}

					$f = 'dp3-' . date('Y-m-d-H-i-s') . '.sql';
					$cmd = $mysqldump_path . " --opt -Q -h{$config['db_host']} -u{$config['db_user']} -p{$config['db_password']} {$config['db_name']} > $f";

					$proc = new \Symfony\Component\Process\Process($cmd, DP_WEB_ROOT . '/data_backups');
					$proc->setTimeout(10000);
					$proc->run(function ($type, $buffer) {
						if ('err' === $type) {
							echo '[ERR] '.$buffer;
						} else {
							echo $buffer;
						}
					});

					if (!$proc->isSuccessful()) {
						$output->writeln('<warn>We detected an error while trying to back up your DeskPRO v3 database. Do you want to continue anyway?</warn>');
						try {
							$yes = $this->getHelper('dialog')->askConfirmation($output, '[y/N]> ', false);
						} catch (\Exception $e) {
							$yes = false;
						}
						if (!$yes) {
							$output->writeln("Aborting. You can re-run this tool once you are ready to proceed.");
							return 25;
						}
					}

					#------------------------------
					# Run the upgrader command
					#------------------------------

					$output->writeln("We are now running through the DeskPRO v3 upgrader. This may take some time.");

					$cmd = $this->getContainer()->getPhpBinaryPath() . ' index.php';
					$dir = DP_ROOT . '/sys/legacy/upgrader';

					$proc = new \Symfony\Component\Process\Process($cmd, $dir);
					$proc->run(function ($type, $buffer) {
						if ('err' === $type) {
							echo '[ERR] '.$buffer;
						} else {
							echo $buffer;
						}
					});

					if (!$proc->isSuccessful() || strpos($proc->getOutput(), 'There was an error determining which build') !== false) {
						$output->writeln(PHP_EOL . '<error>We detected an error while executing the DeskPRO v3 upgrade. You should contact support@deskpro.com.</error>');
						return 26;
					}
				}
			}

			if ($DP_CONFIG['core.filestorage_method'] == 'fs') {
				$this->getContainer()->getDb()->replace('settings', array(
					'name' => 'core.filestorage_method',
					'groupname' => 'core',
					'value' => 'fs',
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				));
			}
		}

		#----------------------------------------
		# Execute import
		#----------------------------------------

		return $this->executeImport($importer, $mode, $page, $input, $output);
	}


	protected function executeImport($importer, $mode, $page, InputInterface $input, OutputInterface $output)
	{
		$logger = $this->logger;
		$php_path = $this->getContainer()->getPhpBinaryPath();

		$start_time = microtime(true);

		#----------------------------------------
		# Run the --info command
		#----------------------------------------

		if ($input->getOption('info')) {
			$output->writeln("Importer: {$importer_class}");
			$output->writeln("Importer ID: {$importer->getId()}");
			$output->writeln("Number of steps: {$importer->countSteps()}");

			for ($i = 1; $i <= $importer->countSteps(); $i++) {
				$output->writeln(sprintf("\t%2s. %s", $i, $importer->getStepTitle($i)));
			}

			$output->writeln('');

			if ($errors = $importer->validateOptions()) {
				$output->writeln("Config errors:");
				foreach ($errors as $e) {
					$output->writeln("<error>\t{$e}</error>");
				}
			}

			return 0;
		}

		#----------------------------------------
		# Execute a single step and page
		#----------------------------------------

		if ($mode == 'exec-step') {
			$importer->validateOptions();
			$importer->setupImport();

			$step_num = $input->getOption('exec-step');
			$importer->preRunStep($step_num);
			$step = $importer->getStep($step_num);
			$step->run($page);
			$importer->postRunStep($step_num);

			$importer->cleanupImport();

			return 0;
		}

		#----------------------------------------
		# Run full importer
		#----------------------------------------

		if ($mode == 'run') {

			$logger->log(sprintf("Starting importer %s", $importer->getId()), 'INFO');

			if ($errors = $importer->validateOptions()) {
				$logger->log(sprintf("There were %i errors detected before importing could begin", count($errors)), 'INFO');
				foreach ($errors as $e) {
					$logger->log($e, 'ERROR');
				}
				return 4;
			}

			$importer->setupImport();
			$logger->log(sprintf("There are %d import steps.", $importer->countSteps()), 'INFO');
			echo "\n";

			if ($importer->isLargeDatabase()) {
				$output->writeln("\n<info>Your database is quite large. Before you continue, we recommend reading our knowledgebase article on importing large databases:\nhttp://www.deskpro.com/g/import-large-db\n</info>");
				try {
					$yes = $this->getHelper('dialog')->askConfirmation($output, 'Do you want to continue with the import now? [Y/n]> ');
				} catch (\Exception $e) {
					$yes = false;
				}
				if (!$yes) {
					echo "\n";
					return 0;
				}
			}

			$i = 1;
			$num = $importer->countSteps();

			if ($input->getOption('step')) {
				$i = $input->getOption('step');
			}

			if ($i < 1 || $i > $importer->countSteps()) {
				$output->writeln("<error>`step` must be between 1 and {$importer->countSteps()}</error>");
				return 1;
			}

			for (; $i <= $num; $i++) {

				$start_step_time = microtime(true);

				$step = $importer->getStep($i);
				$logger->log(sprintf("### Step %d: %s ###", $i, $step::getTitle(), $start_step_time), 'INFO');

				$num_pages = $step->countPages();
				for ($p = 1; $p <= $num_pages; $p++) {
					if ($num_pages > 1) {
						$logger->log(sprintf("Part %d of %d", $p, $num_pages), 'INFO');
					}

					$cmd = $php_path . ' cmd.php dp:import --exec-step=' . $i . ' --exec-step-page=' . $p;
					$proc = new \Symfony\Component\Process\Process($cmd, DP_WEB_ROOT);
					$proc->setTimeout(600);
					$proc->run(function ($type, $buffer) {
						if ('err' === $type) {
							echo '[ERR] '.$buffer;
						} else {
							echo $buffer;
						}
					});

					if (!$proc->isSuccessful()) {
						$logger->log("Error detected, stopping.", 'ERROR');
						return 1;
					}
				}

				$end_step_time = microtime(true);
				$logger->log(sprintf("Step #%d complete: Took %0.3f seconds.", $i, $end_step_time-$start_step_time), 'INFO');
				echo "\n";
			}

			$importer->cleanupImport();

			// Clear caches like kb/news/ideas/files category caches
			$importer->getDb()->executeUpdate('DELETE FROM cache');

			$end_time = microtime(true);
			$logger->log(sprintf("Importer complete. Took %0.3f seconds.", $end_time-$start_time), 'INFO');
			return 0;
		}

		return 0;
	}
}
