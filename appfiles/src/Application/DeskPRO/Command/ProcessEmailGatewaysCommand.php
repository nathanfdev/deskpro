<?php

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Log\Logger;

use Application\DeskPRO\EmailGateway\Reader\EzcReader;

use Orb\Util\Util;
use Orb\Util\Numbers;

class ProcessEmailGatewaysCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected $set_verbose = false;
	protected $ignore_interval = false;
	protected $output;

	protected function configure()
	{
		$this->setName('dp:process-email-gateways');
		$this->addOption('gateway', 'g', InputOption::VALUE_REQUIRED, 'Process this gateway only');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($input->getOption('gateway')) {
			if (Numbers::isInteger($input->getOption('gateway'))) {
				$gateway = App::getEntityRepository('DeskPRO:EmailGateway')->find($input->getOption('gateway'));
			} else {
				$gateway = App::getEntityRepository('DeskPRO:EmailGateway')->getGatewayFromAddress($input->getOption('gateway'));
			}

			if (!$gateway) {
				$output->writeln("<error>No gateway account found with that email address</error>");
				return 2;
			}

			$gateways = array($gateway);
		} else {
			$gateways = App::getEntityRepository('DeskPRO:EmailGateway')->findAll();
		}

		foreach ($gateways as $gateway) {

			/** @var $fetcher \Application\DeskPRO\EmailGateway\Fetcher\AbstractFetcher */
			$fetcher = $gateway->getNewFetcher();

			while ($source = $fetcher->readNext()) {
				$reader = new EzcReader();
				$reader->setRawSource($source['raw_source']);

				App::getOrm()->beginTransaction();

				try {
					/** @var $proc \Application\DeskPRO\EmailGateway\AbstractGateway */
					$proc = $gateway->getNewProcessor($reader);
					$proc->run();

					$source['status'] = 'complete';
					App::getOrm()->persist($source);
					App::getOrm()->flush();

					App::getOrm()->commit();
				} catch (\Exception $e) {
					App::getOrm()->rollback();

					throw $e;
				}
			}
		}
	}
}
