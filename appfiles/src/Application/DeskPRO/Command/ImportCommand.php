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
		$this->addOption('step', null, InputOption::VALUE_REQUIRED, 'Start from this step');
		$this->addOption('exec-step', null, InputOption::VALUE_REQUIRED, 'Execute only this step');
		$this->setHelp("This imports data from another platform into the currently installed helpdesk. Please read http://support.deskpro.com/ for more information.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$mode = null;
		if ($input->getOption('info')) $mode = 'info';
		if ($input->getOption('run')) $mode = 'run';
		if ($input->getOption('step') !== null) $mode = 'step';
		if ($input->getOption('exec-step') !== null) $mode = 'exec-step';

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
		# Set environment
		#----------------------------------------

		error_reporting(E_ALL);
		@ini_set('display_errors', true);
		@ini_set('memory_limit', -1);
		@set_time_limit(0);

		$logger = new Logger();
		$logger->addWriter(new \Orb\Log\Writer\ConsoleOutputWriter($output));

		$start_time = microtime(true);

		/** @var $importer \Application\DeskPRO\Import\Importer\AbstractImporter */
		$importer = new $importer_class($this->getContainer(), $config, $logger);

		#----------------------------------------
		# Run the --info command
		#----------------------------------------

		if ($input->getOption('info')) {
			$output->writeln("Importer: {$importer_class}");
			$output->writeln("Importer ID: {$importer->getId()}");
			$output->writeln("Number of steps: {$importer->countSteps()}");

			if ($errors = $importer->validateOptions()) {
				$output->writeln("Config errors:");
				foreach ($errors as $e) {
					$output->writeln("<error>\t{$e}</error>");
				}
			}

			return 0;
		}

		#----------------------------------------
		# Run importer
		#----------------------------------------

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

		$this->getContainer()->getDb()->beginTransaction();

		$i = 1;
		$num = $importer->countSteps();

		if ($mode == 'step') {
			$i = $input->getOption('step');
		} else if ($mode == 'exec-step') {
			$i = $input->getOption('exec-step');
			$num = $input->getOption('exec-step');
		}

		if ($i < 1 || $i > $importer->countSteps()) {
			$output->writeln("<error>`step` must be between 1 and {$importer->countSteps()}</error>");
			return 1;
		}

		for (; $i <= $num; $i++) {
			$step = $importer->getStep($i);

			$start_step_time = microtime(true);
			$logger->log(sprintf("### Step %d: %s ###", $i, $step->getTitle(), $start_step_time), 'INFO');

			$step->run();

			$end_step_time = microtime(true);
			$logger->log(sprintf("Step #%d complete: Took %0.3f seconds.", $i, $end_step_time-$start_step_time), 'INFO');
			echo "\n";
		}

		$this->getContainer()->getDb()->rollback();

		$importer->cleanupImport();

		$end_time = microtime(true);
		$logger->log(sprintf("Importer complete. Took %0.3f seconds.", $end_time-$start_time), 'INFO');
		return 0;
	}
}
