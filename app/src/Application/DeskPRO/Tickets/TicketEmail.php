<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

use Application\DeskPRO\App;
use Application\DeskPRO\Monolog\NullLogger;
use Application\DeskPRO\TicketLayout\LayoutDisplay;
use Application\DeskPRO\Tickets\Util as TicketUtil;
use Orb\Util\Arrays;
use Orb\Util\CheckedOptionsArray;

class TicketEmail
{
	const MODE_USER  = 'user';
	const MODE_AGENT = 'agent';

	/**
	 * @var \Application\DeskPRO\Settings\Settings
	 */
	private $settings;

	/**
	 * @var \Application\DeskPRO\Mail\Mailer
	 */
	private $mailer;

	/**
	 * @var \Application\DeskPRO\Translate\Translate
	 */
	private $translate;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\CustomFields\TicketFieldManager
	 */
	private $ticket_field_manager;

	/**
	 * @var \Application\DeskPRO\CustomFields\PersonFieldManager
	 */
	private $user_field_manager;

	/**
	 * @var \Application\DeskPRO\TicketLayout\TicketLayoutManager
	 */
	private $ticket_layout_manager;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	private $to_person;

	/**
	 * @var string
	 */
	private $sent_to_email;

	/**
	 * @var string
	 */
	private $sent_to_name;

	/**
	 * @var string[]
	 */
	private $sent_with_ccs;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	private $ticket;

	/**
	 * @var string
	 */
	private $user_mode;

	/**
	 * @var string
	 */
	private $template_name;

	/**
	 * @var string|null
	 */
	private $from_name;

	/**
	 * @var \Application\DeskPRO\Entity\EmailAccount
	 */
	private $from_email_account;

	/**
	 * @var bool
	 */
	private $is_auto;

	/**
	 * @var bool
	 */
	private $do_cc_users = true;

	/**
	 * @var int
	 */
	private $max_attach_size = 0;

	/**
	 * @var \Monolog\Logger
	 */
	private $logger;

	/**
	 * @var array
	 */
	private $headers;

	/**
	 * Use TicketEmailBuilder to build the options array easier.
	 *
	 * @param array $options
	 * @throws \InvalidArgumentException When there are invalid options
	 */
	public function __construct(array $options)
	{
		$opt = new CheckedOptionsArray();
		$opt->addRequiredNames(
			'settings',
			'mailer',
			'translate',
			'em',
			'ticket',
			'to_person',
			'user_mode',
			'template_name'
		);
		$opt->addValidNames(
			'ticket_field_manager',
			'user_field_manager',
			'ticket_layout_manager',
			'from_name',
			'from_email_account',
			'cc_users',
			'is_auto',
			'max_attach_size',
			'logger',
			'headers'
		);
		$opt->setAll($options);
		$opt->ensureRequired();

		$this->to_person               = $opt->get('to_person');
		$this->ticket                  = $opt->get('ticket');
		$this->template_name           = $opt->get('template_name');
		$this->from_name               = $opt->get('from_name', '');

		$this->settings                = $opt->get('settings');
		$this->mailer                  = $opt->get('mailer');
		$this->translate               = $opt->get('translate');
		$this->em                      = $opt->get('em');
		$this->ticket_field_manager    = $opt->get('ticket_field_manager');
		$this->user_field_manager      = $opt->get('user_field_manager');
		$this->ticket_layout_manager   = $opt->get('ticket_layout_manager');
		$this->from_email_account      = $opt->get('from_email_account', null);

		$this->do_cc_users             = $opt->get('cc_users', false);
		$this->is_auto                 = $opt->get('is_auto', false);
		$this->max_attach_size         = $opt->get('max_attach_size', 0);

		$this->user_mode               = $opt->get('user_mode');
		$this->headers                 = $opt->get('headers', array());

		if ($opt->get('user_mode') == 'user') {
			$this->user_mode = 'user';
		} elseif ($opt->get('user_mode') == 'agent') {
			$this->user_mode = 'agent';
		}

		if ($opt->has('logger')) {
			$this->logger = $opt->get('logger');
		} else {
			$this->logger = new NullLogger();
		}

		if ($this->do_cc_users && $this->user_mode == self::MODE_AGENT) {
			throw new \InvalidArgumentException("CC Users does not work on agent emails");
		}

		if ($this->user_mode == self::MODE_AGENT && !$this->to_person->is_agent) {
			throw new \InvalidArgumentException("Agent mode but person is not an agent");
		}
	}


	/**
	 * @return string
	 */
	public function getUserMode()
	{
		return $this->user_mode;
	}


	/**
	 * @return \Application\DeskPRO\Entity\EmailAccount
	 */
	public function getFromEmailAccount()
	{
		return $this->from_email_account;
	}


	/**
	 * @return null|string
	 */
	public function getFromName()
	{
		return $this->from_name;
	}


	/**
	 * @return boolean
	 */
	public function getDoCcUsers()
	{
		return $this->do_cc_users;
	}


	/**
	 * @return string
	 */
	public function getTemplateName()
	{
		return $this->template_name;
	}


	/**
	 * @return \Application\DeskPRO\Entity\Ticket
	 */
	public function getTicket()
	{
		return $this->ticket;
	}


	/**
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function getToPerson()
	{
		return $this->to_person;
	}


	/**
	 * @param array $vars
	 */
	public function send(array $vars = array())
	{
		$mailer     = $this->mailer;
		$mailer->resetLogMessages();

		$translator = $this->translate;
		$em         = $this->em;

		$ticketdisplay = new TicketDisplay($this->ticket, $this->to_person);
		$ticketdisplay->setPersonContext($this->to_person, $this->user_mode);

		if ($this->to_person && $this->to_person->is_agent) {
			$this->to_person->loadHelper('Agent');
			$this->to_person->loadHelper('AgentTeam');
		}

		$vars['ticket']        = $this->ticket;
		$vars['person']        = $this->to_person;
		$vars['ticketdisplay'] = $ticketdisplay;
		$vars['messages']      = array_reverse($ticketdisplay->getMessages());
		$vars['is_auto']       = $this->is_auto;

		if ($this->ticket_layout_manager) {
			$layout_id = $this->ticket->department ? $this->ticket->department->id : null;

			if ($this->user_mode == self::MODE_AGENT) {
				$layout = $this->ticket_layout_manager->getAgentLayouts()->getLayout($layout_id);
				$layout = LayoutDisplay::createFromLayout($layout, LayoutDisplay::VIEW_TICKET, $this->ticket);
			} else {
				$layout = $this->ticket_layout_manager->getUserLayouts()->getLayout($layout_id);
				$layout = LayoutDisplay::createFromLayout($layout, LayoutDisplay::VIEW_TICKET, $this->ticket);
			}

			if ($this->ticket_field_manager) {
				$custom_fields = $this->ticket_field_manager->getDisplayArrayForObject($this->ticket);
			} else {
				$custom_fields = array();
			}

			if ($this->user_field_manager) {
				$custom_user_fields = $this->user_field_manager->getDisplayArrayForObject($this->ticket->person);
			} else {
				$custom_user_fields = array();
			}

			$vars['ticket_layout']      = $layout;
			$vars['custom_fields']      = $custom_fields;
			$vars['custom_user_fields'] = $custom_user_fields;
		}

		$this->logger->info(sprintf("[TicketEmail] Template: %s -- Mode: %s", $this->template_name, $this->user_mode));

		$to_name  = $this->to_person->getDisplayName();

		$state = $this->ticket->getStateChangeRecorder();
		$ticket_attachments = array();
		if ($state->hasNewReply()) {
			$last_message = Arrays::getFirstItem($vars['messages']);

			// This check is because theoretically, the entire thread
			// could be agent notes (e.g., first message was turned into a note).
			// So if this is an email to a user, messages array will be empty
			// and this check will prevent warnings about trying to use a null $last_message.

			if ($last_message) {
				$this->logger->info(sprintf("[TicketEmail] New reply on #%d checking for attachments <= %d", $last_message->id, $this->max_attach_size));
				if (count($last_message->attachments)) {
					$this->logger->info(sprintf("[TicketEmail] Message has %d attachments", count($last_message->attachments)));
					foreach ($last_message->attachments as $a) {
						if ($a->blob->filesize <= $this->max_attach_size) {
							$this->logger->info(sprintf("[TicketEmail] Adding attachment %s", $a->blob->filename));
							$ticket_attachments[$a->id] = $a;
						} else {
							$this->logger->info(sprintf("[TicketEmail] Skipping attachment %s", $a->blob->filename));
						}
					}
				} else {
					$this->logger->info(sprintf("[TicketEmail] Message has no attachments"));
				}
			}

			if ($this->settings->get('core.tickets.enable_feedback') && $this->user_mode == 'user' && $last_message && $last_message->person->is_agent && !$last_message->is_agent_note) {
				$vars['show_rating_link'] = true;
			}
		}

		// To user - use the selected email address on the ticket
		if ($this->user_mode == self::MODE_USER) {
			if ($this->ticket->person_email && $this->ticket->person_email->person === $this->to_person) {
				$to_email = $this->ticket->person_email->email;
				$this->logger->info(sprintf("[TicketEmail] to_email(1): %s", $to_email));
			} else if ($this->ticket->person_email_validating) {
				$to_email = $this->ticket->person_email_validating->email;
				$vars['validating_email'] = $this->ticket->person_email_validating;
				$this->logger->info(sprintf("[TicketEmail] to_email(2): %s -- validating", $to_email));
			} else if ($this->to_person->primary_email) {
				$to_email = $this->to_person->primary_email->email;
				$this->logger->info(sprintf("[TicketEmail] to_email(3): %s", $to_email));
			} else {
				$vars['validating_email'] = $em->getRepository('DeskPRO:PersonEmailValidating')->getForPerson($this->to_person);

				if (!$vars['validating_email']) {
					$this->logger->info(sprintf("[TicketEmail] to_email(4): no email and no validating email"));
					throw new \RuntimeException("no email and no validating email");
				}

				$to_email = $vars['validating_email']->email;
				$this->logger->info(sprintf("[TicketEmail] to_email(4): %s -- validating", $to_email));
			}

		// To agent
		} else {
			if (!$this->to_person || !$this->to_person->primary_email) {
				throw new \RuntimeException("No agent email to send to");
			}

			$to_email = $this->to_person->primary_email->email;
		}

		$tac = null;
		if ($this->user_mode == self::MODE_AGENT) {
			$tac = TicketUtil::getTacForPerson($this->ticket, $this->to_person);
		}
		$vars['tac'] = $tac;

		$this->sent_to_name = $to_name;
		$this->sent_to_email = $to_email;
		$this->sent_with_ccs = array();

		$message = $mailer->createMessage();
		$this->logger->info(sprintf("[TicketEmail] To: %s -- Name: %s", $to_email, $to_name));
		$message->setTo(array($to_email => $to_name));
		$message->setContextId('ticket_gateway');

		if ($ticket_attachments) {
			$vars['attached_blobs'] = $ticket_attachments;
			foreach ($ticket_attachments as $a) {
				$ticketdisplay->setIgnoreAttachment($a);
				$message->attachBlob($a->blob, $a->blob->getDownloadUrl(true));
			}
		}

		$message->setTemplate($this->template_name, $vars);

		if ($this->user_mode == self::MODE_USER && $this->do_cc_users) {
			foreach ($this->ticket->getUserParticipants() as $p) {
				if ($p->getPrimaryEmailAddress()) {
					$cc_email = $p->getPrimaryEmailAddress();
					$cc_name  = $p->getDisplayName();
					if (!$cc_email) {
						continue;
					}

					if ($this->is_auto && $p->disable_autoresponses) {
						$this->logger->info(sprintf("[TicketEmail] CC skipped because autoresponder: %s -- Name: %s", $cc_email, $cc_name));
						continue;
					}

					$this->sent_with_ccs[] = $cc_email;

					$message->addCc($cc_email, $cc_name);
					$this->logger->info(sprintf("[TicketEmail] CC: %s -- Name: %s", $cc_email, $cc_name));
				}
			}
		}

		if (!$this->from_email_account || !$this->from_email_account->outgoing_account) {
			$this->from_email_account = $mailer->getEmailAccountForTicket($this->ticket);
		}

		if (!$this->from_email_account) {
			$this->logger->warning(sprintf("[TicketEmail] No from email to send mail from!"));
			throw new \RuntimeException("No from email to send mail from");
		}

		$from_email = $this->from_email_account->getUseEmailAddress();
		$from_name = $this->from_name;

		$this->logger->info(sprintf("[TicketEmail] From: %s -- Name: %s", $from_email, $from_name));
		$message->setFrom($from_email, $from_name);

		if ($tac) {
			$message->getHeaders()->get('Message-ID')->setId($tac->getUniqueEmailMessageId());
		} else {
			$message->getHeaders()->get('Message-ID')->setId($this->ticket->getUniqueEmailMessageId());
		}

		$message->getHeaders()->addIdHeader('References', $this->ticket->getEmailReferencesHeader());

		if (isset($vars['is_auto']) && $vars['is_auto']) {
			$message->getHeaders()->addTextHeader('X-DeskPRO-Auto', 'Yes');
			$message->setSuppressAutoreplies(true);
			$this->logger->info(sprintf("[TicketEmail] Is auto"));
		}

		if ($this->user_mode == self::MODE_USER) {
			$lang = $this->ticket->getRealLanguage() ?: $this->to_person->getLanguage();
		} else {
			$lang = $this->to_person->getLanguage();
		}

		$translator->setTemporaryLanguage($lang, function() use ($message) {
			$message->prepare();
		});

		foreach ($this->headers as $header) {
			$message->getHeaders()->addTextHeader($header['name'], $header['value']);
		}

		$mailer->send($message);

		foreach ($mailer->getLogMessages() as $log_msg) {
			$this->logger->debug("[TicketEmail][Mailer] $log_msg");
		}
	}


	/**
	 * @return string
	 */
	public function getSentToEmail()
	{
		return $this->sent_to_email;
	}


	/**
	 * @return string
	 */
	public function getSentToName()
	{
		return $this->sent_to_name;
	}


	/**
	 * @return \string[]
	 */
	public function getSentWithCcs()
	{
		return $this->sent_with_ccs;
	}
}