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

class Priority implements LogActionInterface
{
	protected $old_pri;
	protected $new_pri;

	public function __construct($old_pri, $new_pri)
	{
		$this->old_pri = $old_pri;
		$this->new_pri = $new_pri;
	}

	public function getLogName()
	{
		return 'changed_priority';
	}

	public function getLogDetails()
	{
		return array(
			'id_before' => $this->old_pri['id'] ?: null,
			'id_after'  => $this->new_pri['id'] ?: null,

			'old_priority_id' => $this->old_pri['id'],
			'old_priority_title' => $this->old_pri['title'],
			'old_priority_pri' => $this->old_pri['priority'],
			'new_priority_id' => $this->new_pri['id'],
			'new_priority_title' => $this->new_pri['title'],
			'new_priority_pri' => $this->new_pri['priority'],
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}
