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
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

use Application\DeskPRO\Email\TicketUtil;
use Application\DeskPRO\Tickets\TicketChangeTracker;
use Application\DeskPRO\App;

/**
 * Sets agent
 */
abstract class AbstractUserNotificationAction implements ActionInterface
{
	/**
	 * @var \Application\DeskPRO\Tickets\TicketChangeTracker
	 */
	protected $tracker;
	protected $person_context;
	protected $email_sets = array();
	protected $via_message;

	public function __construct(TicketChangeTracker $tracker)
	{
		$this->tracker = $tracker;
	}

	public function getFromAddress(Ticket $ticket)
	{
		if ($ticket->notify_email) {
			$from_email = $ticket->notify_email;
		} else {
			$from_email = App::getSetting('core.default_from_email');
		}

		if ($ticket->notify_email_name) {
			$from_name = $ticket->notify_email_name;
		} else {
			if ($this->via_message) {
				$from_name = $this->via_message->getPerson()->getDisplayName();
			} else {
				$from_name = App::getSetting('core.deskpro_name');
			}
		}

		return array($from_email => $from_name);
	}

	public function setEmailTemplate($tpl, $tpl_type)
	{
		$this->email_sets[$tpl_type] = $tpl;
	}

	public function getTemplate($tpl_type, $default)
	{
		return isset($this->email_sets[$tpl_type]) ? $this->email_sets[$tpl_type] : $default;
	}

	protected function doSend($tpl, $vars, Ticket $ticket, &$change_info = array())
	{
		$person = $ticket->person;

		$parts  = $ticket->getUserParticipants();

		$change_info['emailed'] = array($person);
		$change_info['cced'] = $parts;

		// Is null if not provided,
		// or an array of people ID's if provided (from agent reply)
		$only_cc_ids = $this->tracker->getExtra('enabled_cc');
		$only_cc_ids = null;

		$vars['ticket'] = $ticket;
		$vars['person'] = $person;
		$vars['participants'] = $parts;
		$vars['access_code'] = $ticket->getAccessCode();

		$messages = App::getEntityRepository('DeskPRO:TicketMessage')->getTicketMessages($ticket,array(
			'limit' => 25,
			'order' => 'DESC',
			'with_notes' => false
		));
		$vars['messages'] = $messages;

		$from_address = $this->getFromAddress($ticket);

		App::getTranslator()->setTemporaryLanguage($person->getLanguage(), function($tr, $lang) use ($tpl, $vars, $from_address, $ticket, $person, $parts, $only_cc_ids) {

			$message = App::getMailer()->createMessage();
			$message->setTemplate($tpl, $vars);

			if (!empty($vars['validating_email'])) {
				$message->setTo($vars['validating_email']->getEmail());
			} else {
				$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
			}
			foreach ($parts as $part) {
				$message->addCc($part['email_address'], $part->person->getDisplayName());
			}
			$message->setFrom($from_address);
			$message->getHeaders()->get('Message-ID')->setId($ticket->getUniqueEmailMessageId());

			App::getMailer()->send($message);
		});
	}

	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		return new self($this->tracker);
	}
}
