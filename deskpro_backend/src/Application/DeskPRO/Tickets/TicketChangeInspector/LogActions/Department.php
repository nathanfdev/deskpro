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

class Department implements LogActionInterface
{
	protected $old_dep;
	protected $new_dep;

	public function __construct($old_dep, $new_dep)
	{
		$this->old_dep = $old_dep;
		$this->new_dep = $new_dep;
	}

	public function getLogName()
	{
		return 'changed_department';
	}

	public function getLogDetails()
	{
		return array(
			'id_before' => $this->old_dep['id'] ?: null,
			'id_after'  => $this->new_dep['id'] ?: null,

			'old_department_id' => $this->old_dep['id'],
			'old_department_title' => $this->old_dep['title'],
			'new_department_id' => $this->new_dep['id'],
			'new_department_title' => $this->new_dep['title'],
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}
