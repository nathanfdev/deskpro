<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketChangeInspector\LogActions;

class MessageRemoved implements LogActionInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\TicketAttachment
	 */
	protected $message;

	/**
	 * @var int
	 */
	protected $old_id;

	public function __construct($message)
	{
		$this->message = $message;

		// We need to copy the ID now because after
		// the records have been removed, Doctrine sets the IDs to 0
		$this->old_id = $message->id;
	}

	public function getLogName()
	{
		return 'message_removed';
	}

	public function getLogDetails()
	{
		$details = array();
		$details['id_after'] = $this->old_id;
		$details['message_id'] = $this->old_id;
		$details['is_agent_note'] = $this->message->is_agent_note;
		$details['is_agent_message'] = $this->message->person->is_agent;

		return $details;
	}

	public function getEventType()
	{
		return 'property';
	}
}
