<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
*/

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

class ProcessEmailSourceCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected $set_verbose = false;
	protected $ignore_interval = false;
	protected $output;

	protected function configure()
	{
		$this->setName('dp:process-email-source');
		$this->addOption('source', 'r', InputOption::VALUE_REQUIRED, 'The source ID to process. Without this option, all unprocessed sources will be processed.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$verbose = $input->getOption('verbose');

		if ($input->getOption('source')) {
			$source = App::getEntityRepository('DeskPRO:EmailSource')->find($input->getOption('source'));

			if (!$source) {
				$output->writeln("<error>No source found with ID {$input->getOption('source')}</error>");
				return 2;
			}

			$source_ids = array($source->id);
		} else {
			$source_ids = App::getDb()->fetchAllCol("SELECT id FROM email_sources WHERE status = ? ORDER BY id ASC", array('inserted'));
		}

		$count = count($source_ids);
		$output->writeln("<info>Processing {$count} sources</info>");

		$time_start = microtime(true);

		$logger = new \Orb\Log\Logger();

		if ($verbose) {
			$writer = new \Orb\Log\Writer\Output();
			$logger->addWriter($writer);
		}

		foreach ($source_ids as $source_id) {

			$source = App::getEntityRepository('DeskPRO:EmailSource')->find($source_id);

			$gateway = $source->gateway;

			$reader = new EzcReader();
			$reader->setRawSource($source['raw_source']);
			$reader->setProperty('email_source', $source);

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

		$output->writeln(sprintf("<info>Finished in %.f seconds</info>", microtime(true) - $time_start));

		return 0;
	}
}
