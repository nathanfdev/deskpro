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

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\Actions\NullAction;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckDepartment;
use Application\DeskPRO\Tickets\Triggers\Terms\CheckWorkflow;
use Orb\Log\Logger;
use Orb\Log\Writer\ArrayWriter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;

use Orb\Util\Arrays;
use Orb\Util\Strings;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Route;

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dp:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var \Application\DeskPRO\Tickets\TicketManager $ticket_manager */
		$ticket_manager = App::getContainer()->getSystemService('ticket_manager');

		$person = App::getOrm()->find('DeskPRO:Person', 3);
		$dep    = App::getOrm()->find('DeskPRO:Department', 8);
		$work   = App::getOrm()->find('DeskPRO:TicketWorkflow', 3);

		$ticket = new Ticket();
		$ticket->subject    = "Testing 123 - " . uniqid('', true);
		$ticket->person     = $person;
		$ticket->department = $dep;
		$ticket->workflow   = $work;

		$message = new TicketMessage();
		$message->person = $person;
		$message->setMessageText("Testing 123 - " . uniqid('', true));

		$ticket->addMessage($message);

		$context = $ticket_manager->createUserExecutorContext($person, 'newticket', 'web');
		$ticket_manager->saveTicket($ticket, $context);

		echo "\n";
	}
}
