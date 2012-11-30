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

use Application\DeskPRO\App;

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\Person;

class ReplyAction extends AbstractAction implements PersonContextInterface, PermissionableAction
{
	protected $reply_text;
	protected $attach_ids = array();
	protected $person_context;

	public function __construct($reply_text, array $attach_ids = array())
	{
		$this->reply_text = $reply_text;
		$this->attach_ids = $attach_ids;
	}


	/**
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return void
	 */
	public function setPersonContext(Person $person)
	{
		$this->person_context = $person;
	}


	public function checkPermission(Ticket $ticket, Person $person)
	{
		if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'reply')) {
			return false;
		}

		return true;
	}

	/**
	 * Apply the property to the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function apply(Ticket $ticket)
	{
		if (!$this->person_context) {
			if ($ticket->agent) {
				$this->person_context = $ticket->agent;
			} else {
				// Try to find last agent to replied in tikcet
				$agent_id = App::getDb()->fetchColumn("
					SELECT tickets_messages.person_id
					FROM tickets_messages
					LEFT JOIN people ON (people.id = tickets_messages.person_id)
					WHERE tickets_messages.ticket_id = 1 AND people.is_agent = 1
					ORDER BY tickets_messages.id DESC
				");

				if ($agent_id) {
					$this->person_context = App::getDataService('Agent')->get($agent_id);
				}
			}
		}

		if (!$this->person_context) {
			return;
		}

		$message = new TicketMessage();
		$message->person = $this->person_context;
		$message->message_text = $this->reply_text;
		$ticket->addMessage($message);

		if ($this->attach_ids) {
			foreach ($this->attach_ids as $blob_id) {
				$blob = App::getOrm()->getRepository('DeskPRO:Blob')->find($blob_id);

				if ($blob) {
					$attach = new TicketAttachment();
					$attach['blob'] = $blob;
					$attach['person'] = $this->person_context;

					//$message->addAttachment($attach);
				}
			}
		}
	}


	/**
	 * Get an array of actions that would be performed on the ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 */
	public function getApplyActions(Ticket $ticket)
	{
		return array(
			array('action' => 'reply', 'reply_text' => $this->reply_text, 'attach_ids' => $this->attach_ids)
		);
	}


	/**
	 * Get reply text
	 *
	 * @return int
	 */
	public function getReplyText()
	{
		return $this->reply_text;
	}


	/**
	 * Get attach ids
	 *
	 * @return array
	 */
	public function getAttachIds()
	{
		return $this->attach_ids;
	}


	/**
	 * @param \Application\DeskPRO\Tickets\TicketActions\ActionInterface $other_action
	 * @return \Application\DeskPRO\Tickets\TicketActions\ActionInterface
	 */
	public function merge(ActionInterface $other_action)
	{
		return $other_action;
	}

	/**
	 * @return string
	 */
	public function getDescription($as_html = true)
	{
		$tr = App::getTranslator();

		if ($as_html) {
			$flat = str_replace(array("\r\n", "\n"), ' ', $this->reply_text);
			if (strlen($flat) > 80) $flat = substr($flat, 0, 80) . '...';
			return $tr->phrase('agent.tickets.add_reply_x_action', array('desc' => '<span class="highlight-description">'.htmlspecialchars($flat).'</span>'));
		}
		return $tr->phrase('agent.tickets.add_reply_action');
	}
}
