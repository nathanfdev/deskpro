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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

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
		return array(
			'message_id' => $this->message['id']
		);
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