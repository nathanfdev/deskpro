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
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\ORM\StateChange\StateChangeRecorder as BaseStateChangeRecorder;

class StateChangeRecorder extends BaseStateChangeRecorder
{
	/**
	 * @var array
	 */
	static private $trivial_fields = array(
		'access_codes'             => true,
		'ticket_hash'              => true,
		'date_feedback_rating'     => true,
		'date_created'             => true,
		'date_resolved'            => true,
		'date_closed'              => true,
		'date_first_agent_assign'  => true,
		'date_first_agent_reply'   => true,
		'date_last_agent_reply'    => true,
		'date_last_user_reply'     => true,
		'date_agent_waiting'       => true,
		'date_user_waiting'        => true,
		'date_status'              => true,
		'total_user_waiting'       => true,
		'total_to_first_reply'     => true,
		'locked_by_agent'          => true,
		'date_locked'              => true,
		'has_attachments'          => true,
		'count_agent_replies'      => true,
		'count_user_replies'       => true
	);

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	private $ticket;

	/**
	 * @var bool
	 */
	private $no_id = false;

	/**
	 * If this is a trivial changeset
	 * @var bool
	 */
	private $is_trivial = false;

	/**
	 * When the last trivial check was made
	 * @var null
	 */
	private $is_trivial_checkid = null;

	/**
	 * @param Ticket $ticket
	 */
	public function __construct(Ticket $ticket)
	{
		parent::__construct();

		$this->ticket = $ticket;
		if (!$ticket->id) {
			$this->no_id = true;
		}
	}


	/**
	 * @return bool
	 */
	public function isTrivialChangeSet()
	{
		if ($this->is_trivial_checkid === null || $this->is_trivial_checkid < $this->getStateVersion()) {
			$this->is_trivial = true;
			$this->is_trivial_checkid = $this->getStateVersion();

			foreach ($this->getChangedFields() as $f) {
				if (!isset(self::$trivial_fields[$f])) {
					$this->is_trivial = false;
					break;
				}
			}
		}

		return $this->is_trivial;
	}


	/**
	 * @return bool
	 */
	public function isNewTicket()
	{
		// If the ticket is not a proxy object it means it was created now.
		// - If there was no ID at the time this state recorder was created,
		// it means its part of the same state transaction. (eg state recorder wasnt reset)
		if ($this->no_id && get_class($this->ticket) === 'Application\\DeskPRO\\Entity\\Ticket') {
			return true;
		}

		return false;
	}


	/**
	 * Check if there has been a new reply of type
	 *
	 * @param string $type
	 * @return bool
	 */
	private function hasNewMessageOfType($type)
	{
		if (!$this->hasChangedField('message')) {
			return false;
		}

		foreach (array_reverse($this->getChangesForField('message')) as $change) {
			$message = $change->getNew();
			if (!$message) continue;

			switch ($type) {
				case 'agent_reply':
					if (!$message->is_agent_note && $message->person->is_agent) {
						return true;
					}
					break;
				case 'agent_note':
					if ($message->is_agent_note) {
						return true;
					}
					break;
				case 'user_reply':
					if (!$message->is_agent_note && !$message->person->is_agent) {
						return true;
					}
					break;
			}
		}

		return false;
	}


	/**
	 * Get new messages of type
	 *
	 * @param string $type
	 * @return bool
	 */
	private function getNewMessagesOfType($type = 'any')
	{
		if (!$this->hasChangedField('message')) {
			return array();
		}

		$messages = array();

		foreach (array_reverse($this->getChangesForField('message')) as $change) {
			$message = $change->getNew();
			if (!$message) continue;

			switch ($type) {
				case 'any':
					$messages[] = $message;
					break;

				case 'agent_reply':
					if (!$message->is_agent_note && $message->person->is_agent) {
						$messages[] = $message;
					}
					break;
				case 'agent_note':
					if ($message->is_agent_note) {
						$messages[] = $message;
					}
					break;
				case 'user_reply':
					if (!$message->is_agent_note && !$message->person->is_agent) {
						$messages[] = $message;
					}
					break;
			}
		}

		return $messages;
	}


	/**
	 * Has there been a new agent reply?
	 *
	 * @return bool
	 */
	public function hasNewReply()
	{
		return $this->hasChangedField('message');
	}


	/**
	 * Has there been a new agent reply?
	 *
	 * @return bool
	 */
	public function hasNewAgentReply()
	{
		return $this->hasNewMessageOfType('agent_reply');
	}


	/**
	 * Has there been a new agent note?
	 *
	 * @return bool
	 */
	public function hasNewAgentNote()
	{
		return $this->hasNewMessageOfType('agent_note');
	}


	/**
	 * Has there been a new user reply?
	 *
	 * @return bool
	 */
	public function hasNewUserReply()
	{
		return $this->hasNewMessageOfType('user_reply');
	}


	/**
	 * Get an array of any new repies
	 *
	 * @return \Application\DeskPRO\Entity\TicketMessage[]
	 */
	public function getNewReplies()
	{
		return $this->getNewMessagesOfType('any');
	}


	/**
	 * Get an array of any new agent replies
	 *
	 * @return \Application\DeskPRO\Entity\TicketMessage[]
	 */
	public function getNewAgentReplies()
	{
		return $this->getNewMessagesOfType('agent_reply');
	}


	/**
	 * Get an array of any new agent notes
	 *
	 * @return \Application\DeskPRO\Entity\TicketMessage[]
	 */
	public function getNewAgentNotes()
	{
		return $this->getNewMessagesOfType('agent_note');
	}


	/**
	 * Get an array of any new user replies
	 *
	 * @return \Application\DeskPRO\Entity\TicketMessage[]
	 */
	public function getNewUserReplies()
	{
		return $this->getNewMessagesOfType('user_reply');
	}
}