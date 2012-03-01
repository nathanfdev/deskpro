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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class Message implements LogActionInterface
{
	protected $message;

	public function __construct($message)
	{
		$this->message = $message;
	}

	public function getLogName()
	{
		return 'message_created';
	}

	public function getLogDetails()
	{
		$details = array();
		$details['id_after'] = $this->message['id'];
		$details['message_id'] = $this->message['id'];
		$details['creation_system'] = $this->message['creation_system'];
		$details['is_agent_note'] = $this->message->is_agent_note;
		$details['is_agent_message'] = $this->message->person->is_agent;

		if ($this->message['ip_address']) {
			$details['ip_address'] = $this->message['ip_address'];
		}

		if ($this->message['email']) {
			$details['email'] = $this->message['email'];
		}

		return $details;
	}

	public function getMessage()
	{
		return $this->message;
	}

	public function getEventType()
	{
		return 'message_created';
	}
}
