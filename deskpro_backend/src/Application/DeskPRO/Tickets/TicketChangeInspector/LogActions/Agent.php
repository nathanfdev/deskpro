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

class Agent implements LogActionInterface
{
	protected $old_agent;
	protected $new_agent;

	public function __construct($old_agent, $new_agent)
	{
		$this->old_agent = $old_agent;
		$this->new_agent = $new_agent;
	}

	public function getLogName()
	{
		return 'changed_agent';
	}

	public function getLogDetails()
	{
		return array(
			'id_before' => $this->old_agent['id'] ?: null,
			'id_after'  => $this->new_agent['id'] ?: null,

			'old_agent_id' => $this->old_agent['id'],
			'old_agent_name' => $this->old_agent['display_name'],
			'old_agent_email' => $this->old_agent['primary_email_address'],
			'new_agent_id' => $this->new_agent['id'],
			'new_agent_name' => $this->new_agent['display_name'],
			'new_agent_email' => $this->new_agent['primary_email_address'],
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}
