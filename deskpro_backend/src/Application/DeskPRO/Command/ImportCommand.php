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
			$output->writeln("<error>Choose one of the run modes: --info, --run, --step or --exec-step</error>");
			return 1;
		}

		#----------------------------------------
		# Get importer config
		#----------------------------------------

		$config = App::getConfig('import');
		if (!$config) {
			$output->writeln("<error>There is no `import` configuration.</error>");
			return 1;
		}

		if (!isset($config['importer'])) {
			$output->writeln("<error>Missing `import.importer` configuration value. I do not know which system you want to import from.</error>");
			return 2;
		}

		$importer_class = 'Application\\DeskPRO\\Import\\Importer\\' . $config['importer'] . 'Importer';
		if (!class_exists($importer_class)) {
			$output->writeln("<error>The `import.importer` class of {$config['importer']} does not exist.</error>");
			return 3;
		}

		#----------------------------------------
		# Figure out PHP path
		#----------------------------------------

		$php_path = false;

		if (isset($_SERVER['_']) AND is_executable($_SERVER['_'])) {
			$php_path = $_SERVER['_'];
		}

		if (App::getConfig('php_path')) {
			$php_path = App::getConfig('php_path');
			if (!is_executable($php_path)) {
				$output->writeln("<error>`import.php_path` is invalid</error>");
				return 1;
			}
		}

		if (!$php_path) {
			foreach (array('/usr/bin/php', '/usr/bin/local/php', '/usr/bin/php5', '/usr/bin/local/php5', 'C:\\php\\bin\\php.exe', 'C:\\php5\\bin\\php.exe') as $p) {
				if (is_executable($p)) {
					$php_path = $p;
					break;
				}
			}
		}

		if (!$php_path) {
			$output->writeln("<error>Unknow path to PHP executable. Add `import.php_path` to config.</error>");
			return 1;
		}

		#----------------------------------------
		# Set environment
		#----------------------------------------

		error_reporting(E_ALL | E_STRICT);
		@ini_set('display_errors', true);
		@ini_set('memory_limit', -1);
		@set_time_limit(0);

		$logger = new Logger();
		$logger->addWriter(new \Orb\Log\Writer\ConsoleOutputWriter($output));

		$log_file_path = App::getKernel()->getLogDir() . '/import.log';
		try {
			$logger->addWriter(new \Orb\Log\Writer\Stream($log_file_path));
		} catch (\Exception $e) {
			$output->writeln("<error>Log file not writable: $log_file_path</error>");
			$output->writeln("Make the logs directory writable and try again.");
			return 1;
		}

		// Override default error logger so it logs to the file
		$GLOBALS['DP_ERR_LOGGER'] = $logger;

		$start_time = microtime(true);

		/** @var $importer \Application\DeskPRO\Import\Importer\AbstractImporter */
		$config['log_dir'] = App::getKernel()->getLogDir();
		$config['enable_query_log'] = false;
		$importer = new $importer_class($this->getContainer(), $config, $logger);

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
				$yes = $this->getHelper('dialog')->askConfirmation($output, 'Do you want to continue with the import now? [Y/n]> ');
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

				break;
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
