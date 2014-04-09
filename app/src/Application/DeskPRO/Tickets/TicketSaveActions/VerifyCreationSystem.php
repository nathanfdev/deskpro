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
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class VerifyCreationSystem implements TicketSaveActionInterface
{
	/**
	 * @param Ticket                   $ticket
	 * @param ExecutorContextInterface $context
	 * @return void
	 */
	public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$ticket->creation_system) {
			if ($context->getEventMethod() == 'email') {
				$creation_system = 'gateway.';

				if ($context->getEventPerformer() == 'agent') {
					$creation_system .= 'agent';
				} else {
					$creation_system .= 'person';
				}
			} else if ($context->getEventMethod() == 'api') {
				$creation_system = 'web.api.';

				if ($context->getEventPerformer() == 'agent') {
					$creation_system .= 'agent';
				} else {
					$creation_system .= 'person';
				}
			} else {
				$creation_system = 'web.';

				if ($context->getEventPerformer() == 'agent') {
					$creation_system .= 'agent.portal';
				} else {
					if ($context->getEventMethodOption('is_widget')) {
						$creation_system .= 'person.widget';
					} else if ($context->getEventMethodOption('is_embedded')) {
						$creation_system .= 'person.embed';
					} else {
						$creation_system .= 'person.portal';
					}
				}
			}

			$ticket->creation_system = $creation_system;

			if ($context->getEventMethodOption('origin_url')) {
				$ticket->creation_system_option = $context->getEventMethodOption('origin_url');
			}
		}
	}

}