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

use Application\DeskPRO\EmailGateway\Reader\EzcReader;

use Orb\Util\Util;
use Orb\Util\Numbers;

class ImportCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('dp:import');
		$this->setHelp("This imports data from another platform into the currently installed helpdesk. Please read http://support.deskpro.com/ for more information.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
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

		$importer_class = 'Application\\DeskPRO\\Import\\' . $config['importer'];
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

		#----------------------------------------
		# Run importer
		#----------------------------------------

		$logger = new Logger();
		$logger->addWriter(new \Orb\Log\Writer\ConsoleOutputWriter($output));

		$start_time = microtime(true);

		/** @var $importer \Application\DeskPRO\Import\Importer\AbstractImporter */
		$importer = new $importer_class($this->getContainer(), $config, $logger);

		$logger->log(sprintf("Starting importer %s (%s)", $importer->getId(), $start_time), 'INFO');

		if ($errors = $importer->validateOptions()) {
			$logger->log(sprintf("There were %i errors detected before importing could begin", count($errors)), 'INFO');
			foreach ($errors as $e) {
				$logger->log($e, 'ERROR');
			}
			return 4;
		}

		$importer->setupImport();
		$logger->log(sprintf("There are %d steps.", $importer->countSteps()), 'INFO');

		for ($i = 1; $i <= $importer->countSteps(); $i++) {
			$step = $importer->getStep($i);

			$start_step_time = microtime(true);
			$logger->log(sprintf("Beginning step #%d: %s (%s)", $i, $step->getTitle(), $start_step_time), 'INFO');

			$step->run();

			$end_step_time = microtime(true);
			$logger->log(sprintf("Step #%d complete (%s). Took %0.3f seconds.", $end_step_time, $end_step_time-$start_step_time), 'INFO');
		}

		$importer->cleanupImport();

		$end_time = microtime(true);
		$logger->log(sprintf("Importer complete (%s). Took %0.3f seconds.", $end_time, $end_time-$start_time), 'INFO');
		return 0;
	}
}
