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

class UserNotificationNewTicketValidatingAction extends AbstractUserNotificationAction
{
	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		if ($ticket->person_email_validating) {

			$vars = array(
				'email_subject' => new DelegatePhrase('user.emails.subj_newticket_validate', array('ticket_subject' => $ticket['subject'])),
				'validating_email' => $ticket->person_email_validating,
				'no_cc' => true
			);

			$change_info = array(
				'type' => 'user_notify',
				'notify_type' => 'newticket_validate',
				'emailed' => array($ticket->person_email_validating->getEmail()),
				'cced' => array()
			);

			$this->doSend('DeskPRO:emails_user:new-ticket-validate', $vars, $ticket, $change_info);

			$this->tracker->recordMultiPropertyChanged('log_actions', null, $change_info);

		} else {
			// TODO:permissions agent validation?
			//$this->applyAgentValidating($ticket);
		}
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return '';
	}
}
