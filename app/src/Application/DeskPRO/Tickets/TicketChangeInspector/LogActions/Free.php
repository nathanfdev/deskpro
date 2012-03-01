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

class Free implements LogActionInterface
{
	protected $message;
	protected $data;

	public function __construct($message, array $misc_data = array())
	{
		$this->message = $message;
		$this->data = $misc_data;
	}

	public function getLogName()
	{
		return 'changed_department';
	}

	public function getLogDetails()
	{
		$details = $this->data;
		$details['message'] = $this->message;

		return $details;
	}

	public function getEventType()
	{
		return 'property';
	}
}
