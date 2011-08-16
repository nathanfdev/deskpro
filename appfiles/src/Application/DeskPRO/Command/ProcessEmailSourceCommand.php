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

class ProcessEmailSourceCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected $set_verbose = false;
	protected $ignore_interval = false;
	protected $output;

	protected function configure()
	{
		$this->setName('dp:process-email-source');
		$this->addOption('source', 'r', InputOption::VALUE_REQUIRED, 'The source ID to process');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$verbose = $input->getOption('verbose');

		$source = App::getEntityRepository('DeskPRO:EmailSource')->find($input->getOption('source'));

		if (!$source) {
			$output->writeln("<error>No source found with that ID</error>");
			return 2;
		}

		$gateway = $source['gateway'];

		$reader = new EzcReader();
		$reader->setRawSource($source['raw_source']);
		$reader->setProperty('email_source', $source);

		App::getOrm()->beginTransaction();

		try {
			/** @var $proc \Application\DeskPRO\EmailGateway\AbstractGateway */
			$proc = $gateway->getNewProcessor($reader);
			$created_obj = $proc->run();

			$source['status'] = 'complete';
			App::getOrm()->persist($source);
			App::getOrm()->flush();

			App::getOrm()->commit();

			if ($verbose) {
				if ($created_obj) {
					$output->writeln("Created " . get_class($created_obj) . ": " . $created_obj->getId());
				} else {
					$output->writeln("No object created");
				}
			}
		} catch (\Exception $e) {
			App::getOrm()->rollback();

			throw $e;
		}
	}
}
