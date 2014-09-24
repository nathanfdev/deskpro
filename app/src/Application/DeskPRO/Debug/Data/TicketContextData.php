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
 */

namespace Application\DeskPRO\Debug\Data;

use Application\DeskPRO\App;

class TicketContextData implements DataInterface
{
	public function getData()
	{
		$deps = App::getDb()->fetchAll("SELECT * FROM departments ORDER BY id ASC");
		$teams = App::getDb()->fetchAll("SELECT * FROM agent_teams ORDER BY id ASC");
		$groups = App::getDb()->fetchAll("SELECT * FROM usergroups ORDER BY id ASC");
		$agents = array();
		foreach (App::$container->getAgentData()->getAgents() as $a) {
			$agents[] = $a->toBasicApiData();
		}

		$data = array();
		$data['departments'] = $deps;
		$data['agents']      = $agents;
		$data['agent_teams'] = $teams;
		$data['usergroups']  = $groups;
		$data['usergroups']  = $groups;

		return $data;
	}
}