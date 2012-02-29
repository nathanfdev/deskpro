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

class Workflow implements LogActionInterface
{
	protected $old_work;
	protected $new_work;

	public function __construct($old_work, $new_work)
	{
		$this->old_work = $old_work;
		$this->new_work = $new_work;
	}

	public function getLogName()
	{
		return 'changed_workflow';
	}

	public function getLogDetails()
	{
		return array(
			'id_before' => $this->old_work['id'] ?: null,
			'id_after'  => $this->new_work['id'] ?: null,

			'old_workflow_id'    => $this->old_work ? $this->old_work['id'] : 0,
			'old_workflow_title' => $this->old_work ? $this->old_work['title'] : '',
			'new_workflow_id'    => $this->new_work ? $this->new_work['id'] : 0,
			'new_workflow_title' => $this->new_work ? $this->new_work['title'] : '',
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}
