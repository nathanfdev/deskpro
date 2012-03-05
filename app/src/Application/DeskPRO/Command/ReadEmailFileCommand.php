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

class ReadEmailFileCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected $set_verbose = false;
	protected $ignore_interval = false;
	protected $output;

	protected function configure()
	{
		$this->setName('dp:read-email-file');
		$this->addOption('file', 'f', InputOption::VALUE_REQUIRED, 'Read email in from a file');
		$this->addOption('stdin', 'd', InputOption::VALUE_NONE, 'Read email in from stdin');
		$this->addOption('gateway', 'g', InputOption::VALUE_REQUIRED, 'Force the email to read into this gateway email address. Otherwise the gateway is guessed from the "To" address');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if ($input->getOption('file')) {
			$raw = file_get_contents($input->getOption('file'));
			if (!$raw) {
				$output->writeln("<error>File does not exist or is empty</error>");
				return 1;
			}
		} elseif ($input->getOption('stdin')) {
			$raw = file_get_contents('php://stdin');
			if (!$raw) {
				$output->writeln("<error>No file suppied</error>");
				return 1;
			}
		} else {
			$output->writeln("<error>Choose an input mode with -f or -d</error>");
			return 1;
		}

		$reader = new EzcReader();
		$reader->setRawSource($raw);

		if ($input->getOption('gateway')) {
			$to = array($input->getOption('gateway'));
		} else {
			$to = array();
			foreach ($reader->getToAddresses() as $to_obj) {
				$to[] = $to_obj->email;
			}
		}

		$gateway = App::getEntityRepository('DeskPRO:EmailGateway')->getGatewayFromAddress($to);

		if (!$gateway) {
			$output->writeln("<error>No gateway account found. Tried to find based on these addresses: " . implode(', ', $to) . "</error>");
			return 2;
		}

		$output->writeln(sprintf(
			"<info>Gateway: [%s:%s] %s <%s></info>",
			$gateway['id'],
			Util::getBaseClassname($gateway['processor_class']),
			$gateway['name'],
			$gateway['address']
		));

		/** @var $proc \Application\DeskPRO\EmailGateway\AbstractGatewayProcessor */
		$proc = $gateway->getNewProcessor($reader);
		$proc->run();
	}
}
