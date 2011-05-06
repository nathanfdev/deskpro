<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\App;
use Application\DeskPRO\Translate\DelegatePhrase;

/**
 * Sets agent
 */
class AgentNotificationNewReplyAction extends AbstractAgentNotificationAction
{
	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$agent_ids = $this->getRealSendTo();

		if (!$agent_ids) {
			return;
		}

		foreach ($agent_ids as $agent_id) {
			$agent = App::getEntityRepository('DeskPRO:Person')->find($agent_id);

			$vars = array(
				'email_subject' => new DelegatePhrase('tickets_agent_email.subject_new_reply', array('ticket_subject' => $ticket['subject'])),
			);

			$this->doSend('DeskPRO:emails_agent:new-reply', $vars, $ticket, $person);
		}
	}
}