<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Email\UserNotification;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Used to send email notifications
 *
 * TODO need to sort out 'from' addresses so they use the proper
 * ticket account.
 * TODO: separate notification tpye constants from AgentNotification, since
 * we're using them for user notifications too
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

	public function sendNotifications(array $notify_types)
	{
		$notify_types = Entity\AgentNotification::reduceNotificationTypes($notify_types);
		$person = $this->ticket['person'];

		App::getTranslator()->setLocale($person['locale']);

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
			}
		}

		// Reset translator to current context
		App::getTranslator()->setLocale(null);
	}

	public function sendNewTicket(Entity\Person $person)
	{
		$new_message = App::getEntityRepository('DeskPRO:TicketMessage')->getFirstTicketMessage($this->ticket);
		$email_subject = 'New ticket: ' . $this->ticket['subject'];
		$email_body = App::get('templating')->render('DeskPRO:emails_user:new-ticket.html.twig', array(
			'ticket' => $this->ticket,
			'new_message' => $new_message,
			'subject' => $email_subject,
			'person' => $person
		));

		$message = App::getMailer()->createMessage();
		$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
		$message->setSubject($email_subject);
		$message->setBody($email_body, 'text/html');
		$message->enableQueueHint();

		App::getMailer()->send($message);

		// Send another if the ticket is hidden and needs validation
		if ($this->ticket['hidden_status'] == Entity\Ticket::HIDDEN_STATUS_VALIDATING) {
			$email_subject = 'Validate your email address';
			$email_body = App::get('templating')->render('DeskPRO:emails_user:new-ticket-validate.html.twig', array(
				'ticket' => $this->ticket,
				'subject' => $email_subject,
				'person' => $person
			));

			$message = App::getMailer()->createMessage();
			$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
			$message->setSubject($email_subject);
			$message->setBody($email_body, 'text/html');
			$message->enableQueueHint();

			App::getMailer()->send($message);
		}
	}

	public function sendNewReply(Entity\Person $person)
	{
		$new_message = $this->actions['message_created']->getMessage();
		$email_subject = 'New Reply: ' . $this->ticket['subject'];
		$email_body = App::get('templating')->render('DeskPRO:emails_agent:new-reply.html.twig', array(
			'ticket' => $this->ticket,
			'new_message' => $new_message,
			'subject' => $email_subject,
			'person' => $person,
		));

		$message = App::getMailer()->createMessage();
		$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
		$message->setSubject($email_subject);
		$message->setBody($email_body, 'text/html');
		$message->enableQueueHint();

		App::getMailer()->send($message);
	}
}