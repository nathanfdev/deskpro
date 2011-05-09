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
use Application\DeskPRO\Translate\DelegatePhrase;
use Application\DeskPRO\App;

class UserNotificationNewTicketAction extends AbstractUserNotificationAction
{
	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$user_ids = $this->getRealSendTo();

		if (!$user_ids) {
			return;
		}

		foreach ($user_ids as $user_id) {
			$user = App::getEntityRepository('DeskPRO:Person')->find($user_id);

			if ($user == $ticket->person) {
				$vars = array(
					'email_subject' => new DelegatePhrase('tickets_user_email.subject_new_ticket_confirm', array('ticket_subject' => $ticket['subject'])),
				);

				$this->doSend('DeskPRO:emails_user:new-ticket', $vars, $ticket, $user);
			} else {
				$vars = array(
					'email_subject' => new DelegatePhrase('tickets_user_email.subject_new_ticket_participant', array('ticket_subject' => $ticket['subject'])),
				);

				$this->doSend('DeskPRO:emails_user:ticket-participant', $vars, $ticket, $user);
			}
		}
	}
}