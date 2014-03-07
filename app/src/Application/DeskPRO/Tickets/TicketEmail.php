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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

class TicketEmail
{
	const MODE_USER  = 'user';
	const MODE_AGENT = 'agent';

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	private $to_person;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	private $ticket;

	/**
	 * @var mode
	 */
	private $mode;

	/**
	 * @var string
	 */
	private $template;

	/**
	 * @var string|null
	 */
	private $from_name;

	/**
	 * @var bool
	 */
	private $is_auto;

	/**
	 * @var bool
	 */
	private $do_cc_users = true;

	/**
	 * @param Ticket $ticket     The ticket to send to
	 * @param Person $to_person  Who the email should be sent to
	 * @param string $mode       Context this is being sent in (e.g. agents get notes etc)
	 * @param string $template   Email template to send
	 * @param string $from_name  The from name to send the email with. The from address is automatically fetched based on the ticket values.
	 * @throws \InvalidArgumentException
	 */
	public function __construct(Ticket $ticket, Person $to_person, $mode, $template, $from_name = null)
	{
		$this->ticket    = $ticket;
		$this->to_person = $to_person;
		$this->mode      = $mode;
		$this->template  = $template;
		$this->from_name = $from_name;

		if ($mode == self::MODE_AGENT && !$to_person->is_agent) {
			throw new \InvalidArgumentException("Agent mode but person is not an agent");
		}
	}


	/**
	 * @param bool $is_auto
	 */
	public function setIsAuto($is_auto = true)
	{
		$this->is_auto = (bool)$is_auto;
	}


	/**
	 * Enable CC users
	 * @throws \InvalidArgumentException
	 */
	public function enableCcUsers()
	{
		if ($this->mode == self::MODE_AGENT) {
			throw new \InvalidArgumentException("CC Users does not work on agent emails");
		}

		$this->do_cc_users = true;
	}


	/**
	 * Disable cc users
	 */
	public function disableCcUsers()
	{
		$this->do_cc_users = false;
	}


	/**
	 * @param ExecutorContextInterface $context
	 */
	public function send(ExecutorContextInterface $context, array $vars = array())
	{
		$mailer     = $context->getContainer()->getMailer();
		$translator = $context->getContainer()->getTranslator();
		$em         = $context->getContainer()->getEm();

		$ticketdisplay = new TicketDisplay($this->ticket, $this->to_person);
		$ticketdisplay->setPersonContext($this->to_person, $this->mode);

		$vars['ticket']        = $this->ticket;
		$vars['person']        = $this->to_person;
		$vars['ticketdisplay'] = $ticketdisplay;
		$vars['messages']      = array_reverse($ticketdisplay->getMessages());
		$vars['is_auto']       = $this->is_auto;

		/*
		 * $field_manager = $context->getContainer()->getSystemService('ticket_fields_manager');
		$custom_fields = $field_manager->getDisplayArrayForObject($ticket);

		$ticket_display = new TicketPageZoneCollection('view');
		$ticket_display->addPagesFromDb('agent');
		$page = $ticket_display->getDepartmentPage($ticket->department ? $ticket->department->id : 0);
		$page_display = $page->getPageDisplay('default')->data;
		 *
		 */

		$context->getLogger()->info(sprintf("[TicketEmail] Template: %s -- Mode: %s", $this->template, $this->mode));

		$to_name  = $this->to_person->getDisplayName();

		if ($this->mode == self::MODE_USER && $this->ticket->person_email && $this->ticket->person_email->person == $this->to_person) {
			$to_email = $this->ticket->person_email->email;
			$context->getLogger()->info(sprintf("[TicketEmail] to_email(1): %s", $to_email));
		} else if ($this->ticket->person_email_validating) {
			$to_email = $this->ticket->person_email_validating->email;
			$vars['validating_email'] = $this->ticket->person_email_validating;
			$context->getLogger()->info(sprintf("[TicketEmail] to_email(2): %s -- validating", $to_email));
		} else if ($this->to_person->primary_email) {
			$to_email = $this->to_person->primary_email->email;
			$context->getLogger()->info(sprintf("[TicketEmail] to_email(3): %s", $to_email));
		} else {
			$vars['validating_email'] = $em->getRepository('DeskPRO:PersonEmailValidating')->getForPerson($this->to_person);

			if (!$vars['validating_email']) {
				$context->getLogger()->info(sprintf("[TicketEmail] to_email(4): no email and no validating email"));
				return;
			}

			$to_email = $vars['validating_email']->email;
			$context->getLogger()->info(sprintf("[TicketEmail] to_email(4): %s -- validating", $to_email));
		}

		$tac = null;
		if ($this->mode == self::MODE_AGENT) {
			$tac = TicketUtil::getTacForPerson($this->ticket, $this->to_person);
		}
		$vars['tac'] = $tac;

		$message = $mailer->createMessage();
		$message->setTo(array($to_name => $to_email));
		$context->getLogger()->info(sprintf("[TicketEmail] To: %s -- Name: %s", $to_email, $to_name));
		$message->setContextId('ticket_gateway');
		$message->setTemplate($this->template, $vars);

		if ($this->mode == self::MODE_USER && $this->do_cc_users) {
			foreach ($this->ticket->getUserParticipants() as $p) {
				if ($p->getPrimaryEmailAddress()) {
					$cc_email = $p->getPrimaryEmailAddress();
					$cc_name  = $p->getDisplayName();
					if (!$cc_email) {
						continue;
					}

					$message->addCc($cc_email, $cc_name);
					$context->getLogger()->info(sprintf("[TicketEmail] CC: %s -- Name: %s", $cc_email, $cc_name));
				}
			}
		}

		$from_email = $mailer->getFromAddressForTicket($this->ticket);
		$from_name  = $this->from_name;

		if (!$from_email) {
			$context->getLogger()->info(sprintf("[TicketEmail] No from email to send mail from!"));
			return;
		}

		$context->getLogger()->info(sprintf("[TicketEmail] From: %s -- Name: %s", $from_email, $from_name));
		$message->setFrom($from_email, $from_name);

		if ($tac) {
			$message->getHeaders()->get('Message-ID')->setId($tac->getUniqueEmailMessageId());
		} else {
			$message->getHeaders()->get('Message-ID')->setId($this->ticket->getUniqueEmailMessageId());
		}

		$message->getHeaders()->addIdHeader('References', $$this->ticket->getEmailReferencesHeader());

		if (isset($vars['is_auto']) && $vars['is_auto']) {
			$message->getHeaders()->addTextHeader('X-DeskPRO-Auto', 'Yes');
			$message->setSuppressAutoreplies(true);
			$context->getLogger()->info(sprintf("[TicketEmail] Is auto"));
		}

		$translator->setTemporaryLanguage($this->to_person->getLanguage(), function() use ($message) {
			$message->prepare();
		});

		$mailer->send($message);
	}
}