<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Email\AgentNotification;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Used to send email notifications
 *
 * TODO need to sort out 'from' addresses so they use the proper
 * ticket account.
 */
class Ticket
{
	protected $ticket;
	protected $actions;

	public function __construct(Entity\Ticket $ticket, array $actions = array())
	{
		$this->ticket = $ticket;
		$this->actions = $actions;
	}

	public function sendNotifications(array $notifications)
	{
		foreach ($notifications as $person_id => $notify_types) {
			$person = App::getEntityRepository('DeskPRO:Person')->find($person_id);
			if (!$person['primary_email']) {
				continue;
			}

			App::getTranslator()->setLocale($person['locale']);

			$notify_types = Entity\AgentNotification::reduceNotificationTypes($notify_types);

			foreach ($notify_types as $type) {
				switch ($type) {
					case Entity\AgentNotification::NOTIFY_NEW_TICKET:
						$this->sendNewTicket($person);
						break;

					case Entity\AgentNotification::NOTIFY_NEW_REPLY:
						$this->sendNewReply($person);
						break;

					case Entity\AgentNotification::NOTIFY_NEW_AGENT_REPLY:
						$this->sendNewReply($person);
						break;

					case Entity\AgentNotification::NOTIFY_PROPERTY_CHANGE:
						$this->sendPropChange($person);
						break;
				}
			}
		}

		// Reset translator to current context
		App::getTranslator()->setLocale(null);
	}

	public function sendNewTicket(Entity\Person $person)
	{
		$new_message = App::getEntityRepository('DeskPRO:TicketMessage')->getFirstTicketMessage($this->ticket);
		$email_subject = 'New Ticket: ' . $this->ticket['subject'];
		$email_body = App::get('templating')->render('DeskPRO:emails_agent:new-ticket.html.twig', array(
			'ticket' => $this->ticket,
			'new_message' => $new_message,
			'subject' => $email_subject,
			'agent' => $person,
			'access_code' => $this->ticket->findAccessCodeForPerson($person)->getCode()
		));

		echo $email_body;
		$message = App::getMailer()->createMessage();
		$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
		$message->setSubject($email_subject);
		$message->setBody($email_body, 'text/html');
		$message->enableQueueHint();

		App::getMailer()->send($message);
	}

	public function sendNewReply(Entity\Person $person)
	{
		$new_message = $this->actions['message_created']->getMessage();
		$changes = $this->_getPropertyChanges();
		$email_subject = 'New Reply: ' . $this->ticket['subject'];
		$email_body = App::get('templating')->render('DeskPRO:emails_agent:new-reply.html.twig', array(
			'ticket' => $this->ticket,
			'new_message' => $new_message,
			'subject' => $email_subject,
			'agent' => $person,
			'ticket_diff' => $this->_getPropertyChanges(),
			'access_code' => $this->ticket->findAccessCodeForPerson($person)->getCode()
		));

		$message = App::getMailer()->createMessage();
		$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
		$message->setSubject($email_subject);
		$message->setBody($email_body, 'text/html');
		$message->enableQueueHint();

		App::getMailer()->send($message);
	}

	public function sendPropChange(Entity\Person $person)
	{
		$changes = $this->_getPropertyChanges();
		$email_subject = 'Ticket Changed: ' . $this->ticket['subject'];
		$email_body = App::get('templating')->render('DeskPRO:emails_agent:new-reply.html.twig', array(
			'ticket' => $this->ticket,
			'subject' => $email_subject,
			'agent' => $person,
			'ticket_diff' => $this->_getPropertyChanges(),
			'access_code' => $this->ticket->findAccessCodeForPerson($person)->getCode()
		));

		$message = App::getMailer()->createMessage();
		$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
		$message->setSubject($email_subject);
		$message->setBody($email_body, 'text/html');
		$message->enableQueueHint();

		App::getMailer()->send($message);
	}

	protected function _getPropertyChanges()
	{
		$diff = array();

		foreach ($this->actions as $action) {
			if ($action->getEventType() != 'property') continue;
			$diff[$action->getgetLogName()] = $action->getLogDetails();
		}

		return $diff;
	}
}