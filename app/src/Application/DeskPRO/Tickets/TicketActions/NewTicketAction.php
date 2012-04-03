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
 * @subpackage Tickets
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;

use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\Translate\DelegatePhrase;
use Application\DeskPRO\App;

/**
 * This action handles toggling email validation features,
 * and handles sending auto-response to users
 */
class NewTicketAction implements BreakableAction, ActionInterface
{
	/**
	 * True to enable email validation on accounts that have not been validated yet.
	 *
	 * @var bool
	 */
	protected $enable_validation = false;

	/**
	 * @var string
	 */
	protected $validating_email_tpl = 'DeskPRO:emails_user:new-ticket-validate.html.twig';

	/**
	 * @var string
	 */
	protected $newticket_email_tpl = 'DeskPRO:emails_user:new-ticket.html.twig';

	/**
	 * @var string
	 */
	protected $newticket_agent_email_tpl = 'DeskPRO:emails_user:new-ticket-byagent.html.twig';

	/**
	 * @var bool
	 */
	protected $enable_notify = true;

	/**
	 * @var bool
	 */
	protected $do_break = false;

	protected $op_mode = 'run';

	/**
	 * @var \Application\DeskPRO\Tickets\TicketChangeTracker
	 */
	protected $tracker;
	protected $person_context;

	public function __construct(TicketChangeTracker $tracker, $mode = 'run')
	{
		$this->tracker = $tracker;
		$this->op_mode = $mode;
	}

	/**
	 * Enable email notifications (auto-reply)
	 */
	public function enableNotifications()
	{
		$this->enable_notify = true;
	}


	/**
	 * Disable email notifications
	 */
	public function disableNotifications()
	{
		$this->enable_notify = false;
	}

	/**
	 * Enable email validation on new accounts
	 */
	public function enableValidation()
	{
		$this->enable_validation = true;
	}


	/**
	 * Disable email validation on new accounts
	 */
	public function disableValidation()
	{
		$this->enable_validation = false;
	}


	/**
	 * @param string $tpl
	 */
	public function setValidationEmailTemplate($tpl)
	{
		$this->validating_email_tpl = $tpl;
	}


	/**
	 * @param string $tpl
	 */
	public function setEmailTemplate($tpl, $type = '')
	{
		$this->tracker->logMessage("set template $tpl $type");
		switch ($type) {
			case 'user_new_ticket':
				$this->newticket_email_tpl = $tpl;
				break;
			case 'user_new_ticket_validate':
				$this->validating_email_tpl = $tpl;
				break;
			case 'user_new_ticket_agent':
				$this->newticket_agent_email_tpl = $tpl;
				break;
		}
	}


	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		$validating = false;
		if ($ticket->person_email_validating) {

			// We always insert new addresses as validating first
			// So if its new, then we only enable actual validation when the enable_validation
			// flag was set
			if ($this->enable_validation && $ticket->person_email_validating->isNewEntity()) {
				$validating = true;

			// If its an existing validating email address, then we dont change its status,
			// its still validating no matter what the flag says
			} elseif (!$ticket->person_email_validating->isNewEntity()) {
				$validating = true;
			}
		}

		$this->tracker->logMessage("[NewTicketAction] Mode: " . $this->op_mode);

		$this->tracker->logMessage("[NewTicketAction] Validating: " . ($validating ? 'yes' : 'no'));

		if ($this->op_mode == 'pre') {
			$this->op_mode = 'run';
			if ($validating) {
				$ticket->setStatus('hidden.validating');
			} else {
				$ticket->setStatus('awaiting_agent');
			}

			App::getOrm()->persist($ticket);
			App::getOrm()->flush();
			return;
		}

		if ($validating) {
			$this->applyValidating($ticket);
			return;
		}


		#------------------------------
		# Convert a validating email address into a real one
		#------------------------------

		// If we got here with a validating email address it means the
		// address is new, but we dont require validation.
		// So we'll make it a real address right now.

		if ($ticket->person_email_validating) {
			$email = new PersonEmail();
			$email->email = $ticket->person_email_validating->email;
			$email->date_created = $ticket->person_email_validating->date_created;
			$email->date_validated = new \DateTime();
			$email->person = $ticket->person;

			$ticket->person->addEmailAddress($email);

			App::getOrm()->persist($email);

			$ticket->person_email_validating = null;
			$ticket->person_email = $email;

			App::getOrm()->persist($ticket);
			App::getOrm()->flush();
		}

		#------------------------------
		# If we havent disabled notifications,
		# send the auto-reply
		#------------------------------

		if ($this->enable_notify) {

			if ($ticket->creation_system == Ticket::CREATED_WEB_AGENT) {
				$tpl = $this->newticket_agent_email_tpl;
			} else {
				$tpl = $this->newticket_email_tpl;
			}

			$this->tracker->logMessage("[NewTicketAction] Sending email " . $tpl);

			$person       = $ticket->person;
			$from_address = $this->getFromAddress($ticket);

			$vars = array(
				'ticket' => $ticket,
				'person' => $person,
			);

			$messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages($ticket,array(
				'limit' => 25,
				'order' => 'DESC',
				'with_notes' => false
			));
			$vars['messages'] = $messages;

			App::getTranslator()->setTemporaryLanguage($person->getLanguage(), function($tr, $lang) use ($tpl, $vars, $from_address, $ticket, $person) {
				$message = App::getMailer()->createMessage();
				$message->setTemplate($tpl, $vars);
				$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
				$message->setFrom($from_address);
				$message->getHeaders()->get('Message-ID')->setId($ticket->getUniqueEmailMessageId());

				App::getMailer()->send($message);
			});
		} else {
			$this->tracker->logMessage("[NewTicketAction] No notification");
		}
	}


	/**
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function applyValidating(Ticket $ticket)
	{
		// Signal to stop processing triggers
		$this->do_break = true;

		$tpl          = $this->validating_email_tpl;
		$person       = $ticket->person;
		$from_address = $this->getFromAddress($ticket);

		$this->tracker->logMessage("[NewTicketAction] Sending $tpl " . $tpl);

		$vars = array(
			'ticket' => $ticket,
			'person' => $person,
		);

		$messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages($ticket,array(
			'limit' => 25,
			'order' => 'DESC',
			'with_notes' => false
		));
		$vars['messages'] = $messages;

		App::getTranslator()->setTemporaryLanguage($person->getLanguage(), function($tr, $lang) use ($tpl, $vars, $from_address, $ticket, $person) {
			$message = App::getMailer()->createMessage();
			$message->setTemplate($tpl, $vars);
			$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
			$message->setFrom($from_address);
			$message->getHeaders()->get('Message-ID')->setId($ticket->getUniqueEmailMessageId());

			App::getMailer()->send($message);
		});
	}

	public function shouldBreakAction()
	{
		return $this->do_break;
	}

	/**
	 * @return string
	 */
	public function getFromAddress(Ticket $ticket)
	{
		if ($ticket->notify_email) {
			return $ticket->notify_email;
		}

		return App::getSetting('core.default_from_email');
	}


	/**
	 * @return string
	 */
	public function getDescription($as_html = true)
	{
		return '';
	}

	public function merge(ActionInterface $other_action)
	{
		return null;
	}
}
