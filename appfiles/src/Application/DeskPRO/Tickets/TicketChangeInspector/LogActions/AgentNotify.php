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

class AgentNotify implements LogActionInterface
{
	protected $type;
	protected $who_emailed;

	public function __construct(array $info)
	{
		$this->type = $info['notify_type'];
		$this->who_emailed = $info['emailed'];
	}

	public function getLogName()
	{
		return 'agent_notify';
	}

	public function getLogDetails()
	{
		$details = array();
		$details['type'] = $this->type;
		$details['who_emailed'] = array();

		foreach ($this->who_emailed as $person) {
			$details['who_emailed'][] = array(
				'person_id'    => $person['id'],
				'person_name'  => $person['display_name'],
				'person_email' => $person['primary_email_address']
			);
		}

		return $details;
	}

	public function getEventType()
	{
		return 'agent_notify';
	}
}