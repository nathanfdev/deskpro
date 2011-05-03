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

/**
 * Sets agent
 */
class AgentNotificationPropertyChangeAction extends AbstractAgentNotificationAction
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
			$tac = $this->ticket->findAccessCodeForPerson($person);

			$changes = $this->tracker->getAllChangedProperties();
			$email_subject = 'Ticket Changed: ' . $this->ticket['subject'];
			$email_body = App::get('templating')->render('DeskPRO:emails_agent:prop-change'.$this->getTemplateSuffix().'.html.twig', array(
				'ticket' => $this->ticket,
				'subject' => $email_subject,
				'agent' => $person,
				'ticket_diff' => $changes,
				'access_code' => $tac['code'],
				'access_code_full' => $this->ticket['ref'] . '-' . $tac['code']
			));

			$message = App::getMailer()->createMessage();
			$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
			$message->setSubject($email_subject);
			$message->setBody($email_body, 'text/html');
			$message->enableQueueHint();

			App::getMailer()->send($message);
		}
	}
}