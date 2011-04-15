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

class Status implements LogActionInterface
{
	protected $old_status;
	protected $new_status;

	public function __construct($old_status, $new_status)
	{
		$this->old_status = $old_status;
		$this->new_status = $new_status;
	}

	public function getLogName()
	{
		return 'changed_status';
	}

	public function getLogDetails()
	{
		return array(
			'old_status' => $this->old_status,
			'new_status' => $this->new_status,
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}