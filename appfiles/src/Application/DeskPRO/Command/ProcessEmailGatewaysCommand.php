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

class ProcessEmailGatewaysCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected $set_verbose = false;
	protected $ignore_interval = false;
	protected $output;

	protected function configure()
	{
		$this->setName('dp:process-email-gateways');
		$this->addOption('gateway', 'g', InputOption::VALUE_REQUIRED, 'Process this gateway ID only');
		$this->addOption('force', 'f', InputOption::VALUE_NONE, 'Process the gateway even if its disabled');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$verbose = $input->getOption('verbose');

		if ($input->getOption('gateway')) {
			$gateway = App::getEntityRepository('DeskPRO:EmailGateway')->find($input->getOption('gateway'));

			if (!$gateway) {
				$output->writeln("<error>No gateway account found with that ID</error>");
				return 2;
			}

			if (!$gateway->is_enabled && !$input->getOption('force')) {
				$output->writeln('<error>Gateway is currently disabled. Use --force to run it anyway.');
				return 3;
			}

			$gateways = array($gateway);
		} else {
			if ($input->getOption('force')) {
				$gateways = App::getEntityRepository('DeskPRO:EmailGateway')->findAll();
			} else {
				$gateways = App::getEntityRepository('DeskPRO:EmailGateway')->getAllEnabled();
			}
		}

		$count = count($gateways);
		if ($verbose) {
			$output->writeln("<info>{$count} gateways found</info>");
		}

		$logger = new \Application\DeskPRO\Log\Logger();

		$output_writer = new \Orb\Log\Writer\ConsoleOutputWriter($output);
		$logger->addWriter($output_writer);
		if (!$verbose) {
			$output_writer->addFilter(new \Orb\Log\Filter\PriorityFilter(Logger::NOTICE));
		}

		$runner = new \Application\DeskPRO\EmailGateway\Runner();
		$runner->setLogger($logger);
		$runner->setGateways($gateways);
		$runner->execute();

		return 0;
	}
}
