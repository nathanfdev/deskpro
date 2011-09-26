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
		$change_info = array(
			'type' => 'user_notify',
			'notify_type' => 'newticket',
			'emailed' => array(),
			'cced' => array()
		);

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

			$person = $ticket->person;

			App::getTranslator()->setTemporaryLanguage($person->getLangauge(), function($tr, $lang) use ($tpl, $vars, $ticket, $person) {
				$email_subject = $tr->phrase($vars['email_subject']);
				$email_body = App::get('templating')->render('DeskPRO:emails_user:new-ticket-validate.html.twig', $vars);

				$message = App::getMailer()->createMessage();
				$message->setTo($ticket->person_email_validating->getEmail(), $person->getDisplayName());
				$message->setSubject($email_subject);
				$message->setBody($email_body, 'text/html');
				$message->enableQueueHint();

				App::getMailer()->send($message);
			});

			$this->tracker->recordMultiPropertyChanged('log_actions', null, $change_info);

		} else {
			// TODO agent validation?
			$this->applyAgentValidating($ticket);
		}
	}
}
