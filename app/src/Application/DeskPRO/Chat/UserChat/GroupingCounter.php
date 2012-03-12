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
 * @subpackage Tickets
 */

namespace Application\DeskPRO\Chat\UserChat;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

class GroupingCounter
{
	protected $group_by;
	protected $searcher;
	protected $groups = array(
		'none' => '',
		'department' => 'department_id',
		'agent' => 'agent_id',
		'date_created' => 'date_created'
	);

	public function __construct($group_by)
	{
		$this->group_by = $this->groups[$group_by];
	}

	public function getCounts($searcher)
	{
		if(empty($this->group_by))
			return array();

		$searcher->setGroupBy($this->group_by);

		switch($this->group_by) {
			case 'agent_id':
				$searcher->addJoin('people ON agent_id = people.id');
				$searcher->setColumns('agent_id AS id, COALESCE(people.name, "Unassigned") AS title, COUNT(*) AS count');
				break;
			case 'department_id':
				$searcher->addJoin('departments ON department_id = departments.id');
				$searcher->setColumns('department_id AS id, departments.title AS title, COUNT(*) AS count');
				break;
			case 'date_created':
				$searcher->setGroupBy('MONTH(date_created), YEAR(date_created)');
				$searcher->setColumns('DATE_FORMAT(date_created, "%M-%Y") AS id, DATE_FORMAT(date_created,"%M %Y") AS title, COUNT(*) AS count');
				$searcher->setOrderBy('chat_conversations.date_created');
				break;
		}

		$db = App::getDb();
		$counts = $db->fetchAll($searcher->getSql());

		return $counts;
	}
}