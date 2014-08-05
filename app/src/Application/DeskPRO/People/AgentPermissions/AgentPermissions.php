<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions;

use Application\DeskPRO\People\AgentPermissions\Value\ChatPermissions;
use Application\DeskPRO\People\AgentPermissions\Value\GeneralPermissions;
use Application\DeskPRO\People\AgentPermissions\Value\OrgPermissions;
use Application\DeskPRO\People\AgentPermissions\Value\PeoplePermissions;
use Application\DeskPRO\People\AgentPermissions\Value\PublishPermissions;
use Application\DeskPRO\People\AgentPermissions\Value\TicketPermissions;

class AgentPermissions
{
	/**
	 * @var \Application\DeskPRO\People\AgentPermissions\Value\ChatPermissions
	 */
	public $chat;

	/**
	 * @var \Application\DeskPRO\People\AgentPermissions\Value\GeneralPermissions
	 */
	public $general;

	/**
	 * @var \Application\DeskPRO\People\AgentPermissions\Value\OrgPermissions
	 */
	public $org;

	/**
	 * @var \Application\DeskPRO\People\AgentPermissions\Value\PeoplePermissions
	 */
	public $people;

	/**
	 * @var \Application\DeskPRO\People\AgentPermissions\Value\PublishPermissions
	 */
	public $publish;

	/**
	 * @var \Application\DeskPRO\People\AgentPermissions\Value\TicketPermissions
	 */
	public $ticket;

	public function __construct()
	{
		$this->chat    = new ChatPermissions();
		$this->general = new GeneralPermissions();
		$this->org     = new OrgPermissions();
		$this->people  = new PeoplePermissions();
		$this->publish = new PublishPermissions();
		$this->ticket  = new TicketPermissions();
	}


	/**
	 * @return Value\PermissionValueInterface[]
	 */
	public function getCollections()
	{
		return array(
			$this->chat,
			$this->general,
			$this->org,
			$this->people,
			$this->publish,
			$this->ticket,
		);
	}


	/**
	 * @return array
	 */
	public function toArray()
	{
		$arr = array();
		foreach (array('chat', 'general', 'org', 'people', 'publish', 'ticket') as $prop) {
			$arr[$prop] = array();
			foreach ($this->$prop->getNames() as $name) {
				$arr[$prop][$name] = (bool)$this->$prop->$name;
			}
		}

		return $arr;
	}


	/**
	 * Reads perms in from an array
	 *
	 * @param array $perms
	 */
	public function fromArray(array $perms)
	{
		foreach (array('chat', 'general', 'org', 'people', 'publish', 'ticket') as $prop) {
			if (!isset($perms[$prop])) continue;

			foreach ($this->$prop->getNames() as $name) {
				$this->$prop->$name = isset($perms[$prop][$name]) ? ((bool)$perms[$prop][$name]) : false;
			}
		}
	}
}