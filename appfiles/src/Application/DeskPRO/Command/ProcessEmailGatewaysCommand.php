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
		$force = $input->getOption('force');

		if ($input->getOption('gateway')) {
			$gateway = App::getEntityRepository('DeskPRO:EmailGateway')->find($input->getOption('gateway'));

			if (!$gateway) {
				$output->writeln("<error>No gateway account found with that ID</error>");
				return 2;
			}

			$gateways = array($gateway);
		} else {
			$gateways = App::getEntityRepository('DeskPRO:EmailGateway')->findAll();
		}

		$count = count($gateways);
		if ($verbose) {
			$output->writeln("<info>{$count} gateways found</info>");
		}

		$time_start = microtime(true);

		foreach ($gateways as $gateway) {

			if ($verbose) {
				$output->writeln("<info>Processing: [{$gateway['id']}] {$gateway['title']} {$gateway['gateway_type']}:{$gateway['connection_type']}</info>");
			}

			if (!$gateway->is_enabled) {
				if (!$force) {
					if ($verbose) {
						$output->writeln("<info>Gateway disabled. Skipping.</info>");
					}
					continue;
				}

				if ($verbose) {
					$output->writeln("<info>Gateway disabled but --force enabled so processing anyway</info>");
				}
			}

			/** @var $fetcher \Application\DeskPRO\EmailGateway\Fetcher\AbstractFetcher */
			$fetcher = $gateway->getFetcher();

			$logger = new \Orb\Log\Logger();
			if ($verbose) {
				$writer = new \Orb\Log\Writer\Output();
				$logger->addWriter($writer);
			}

			while ($source = $fetcher->readNext()) {

				if ($verbose) {
					$output->writeln("Read source ID {$source['id']}");
				}

				$reader = new EzcReader();
				$reader->setRawSource($source['raw_source']);
				$reader->setProperty('email_source', $source);

				if ($verbose) {
					$to = array();
					foreach ($reader->getToAddresses() as $x) {
						$to[] = $x->getEmail();
					}
					$to = implode(', ', $to);

					$subj = substr($reader->getSubject()->getSubject(), 0, 40);

					$output->writeln("[Message] To: $to :: $subj");
				}

				App::getOrm()->beginTransaction();

				try {
					/** @var $proc \Application\DeskPRO\EmailGateway\AbstractGatewayProcessor */
					$proc = $gateway->getNewProcessor($reader, array('logger' => $logger));
					$created_obj = $proc->run();

					$source['status'] = 'complete';
					App::getOrm()->persist($source);
					App::getOrm()->flush();

					App::getOrm()->commit();

					if ($verbose) {
						$output->writeln("Created " . get_class($created_obj) . ": " . $created_obj->getId());
					}
				} catch (\Exception $e) {
					App::getOrm()->rollback();

					throw $e;
				}
			}
		}

		if ($verbose) {
			$output->writeln(sprintf("<info>Finished in %.f seconds</info>", microtime(true) - $time_start));
		}

		return 0;
	}
}
