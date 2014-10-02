<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\DeskPRO\People;

class PermissionMatrix
{
	/**
	 * @var \Application\DeskPRO\Entity\Person[]
	 */
	protected $agents;

	/**
	 * @var \Application\DeskPRO\Entity\Usergroup[]
	 */
	protected $agent_groups;

	/**
	 * @var \Application\DeskPRO\Entity\Usergroup[]
	 */
	protected $user_groups;

	/**
	 * @var array
	 */
	private $agent_to_groups;

	/**
	 * @var array
	 */
	private $agentgroup_to_agents;

	/**
	 * @var array
	 */
	private $agent_perms;

	/**
	 * @var array
	 */
	private $agentgroup_perms;

	/**
	 * @var array
	 */
	private $usergroup_perms;


	/**
	 * @param \Application\DeskPRO\Entity\Person[] $agents
	 * @param \Application\DeskPRO\Entity\Usergroup[] $groups
	 */
	public function __construct($agents, $groups)
	{
		$this->agent_to_agentgroups = array();
		$this->agentgroup_to_agents = array();

		foreach ($groups as $g) {
			if ($g->is_agent_group) {
				$this->agentgroup_to_agents[$g->id] = array();
				$this->agent_groups[$g->id] = $g;
			} else {
				$this->user_groups[$g->id] = $g;
			}
		}

		foreach ($agents as $a) {
			$this->agents[$a->id] = $a;
			$this->agent_to_groups[$a->id] = array();

			foreach ($this->agent_groups as $g) {
				if ($a->hasUsergroup($g)) {
					$this->agent_to_agentgroups[$a->id][$g->id] = $g->id;
					$this->agentgroup_to_agents[$g->id][$a->id] = $a->id;
				}
			}
		}
	}


	/**
	 * Set permissions from a "permission" array (eg including raw department_permissions records from the db)
	 *
	 * <code>
	 * array('usergroup_id' => 1, 'name' => 'use', 'value' => 1),
	 * array('usergroup_id' => 2, 'name' => 'use', 'value' => 1),
	 * array('usergroup_id' => 3, 'name' => 'use', 'value' => 1),
	 * array('person_id' => 55, 'name' => 'use', 'value' => 1),
	 * </code>
	 *
	 * @param array|\Doctrine\Common\Collections\ArrayCollection $records
	 */
	public function setPermArray($records)
	{
		$this->agent_perms      = array();
		$this->agentgroup_perms = array();
		$this->usergroup_perms  = array();

		foreach($records as $rec) {
			if (empty($rec['value']) || !$rec['value']) {
				continue;
			}
			if (empty($rec['name']) || !$rec['name']) {
				$rec['name'] = 'use';
			}

			if (!empty($rec['usergroup_id']) && $rec['usergroup_id']) {
				if (isset($this->agent_groups[$rec['usergroup_id']])) {
					$g = $this->agent_groups[$rec['usergroup_id']];

					if (!isset($this->agentgroup_perms[$g->id])) {
						$this->agentgroup_perms[$g->id] = array();
					}
					$this->agentgroup_perms[$g->id][$rec['name']] = 1;
				} elseif (isset($this->user_groups[$rec['usergroup_id']])) {
					$g = $this->user_groups[$rec['usergroup_id']];

					if (!isset($this->usergroup_perms[$g->id])) {
						$this->usergroup_perms[$g->id] = array();
					}
					$this->usergroup_perms[$g->id][$rec['name']] = 1;
				}
			} else if (!empty($rec['person_id']) && $rec['person_id'] && isset($this->agents[$rec['person_id']])) {
				if (!isset($this->agent_perms[$rec['person_id']])) {
					$this->agent_perms[$rec['person_id']] = array();
				}

				$this->agent_perms[$rec['person_id']][$rec['name']] = 1;
			}
		}
	}

	/**
	 * Set permission from an array of permission objects (e.g., DepartmentPermission)
	 *
	 * @param $records
	 */
	public function setPermRecords($records)
	{
		$array = array();

		foreach ($records as $rec) {
			$array[] = array(
				'usergroup_id' => !empty($rec->usergroup) ? $rec->usergroup->id : null,
				'person_id'    => !empty($rec->person) ? $rec->person->id : null,
				'name'         => $rec->name,
				'value'        => $rec->value
			);
		}
	}


	/**
	 * Re-writes the set permissions into a reduced set of permissions based on
	 * agent group membership.
	 *
	 * Returns an array:
	 *
	 * <code>
	 * array('usergroup_id' => 1, 'name' => 'use', 'value' => 1),
	 * array('usergroup_id' => 2, 'name' => 'use', 'value' => 1),
	 * array('usergroup_id' => 3, 'name' => 'use', 'value' => 1),
	 * array('person_id' => 55, 'name' => 'use', 'value' => 1),
	 * </code>
	 *
	 * @return array
	 */
	public function getPermsArray()
	{
		$set_perms = array();

		foreach ($this->agentgroup_perms as $ugid => $perms) {
			foreach ($perms as $perm_name => $perm_value) {
				$set_perms[] = array('usergroup_id' => $ugid, 'name' => $perm_name, 'value' => 1);
			}
		}
		foreach ($this->usergroup_perms as $ugid => $perms) {
			foreach ($perms as $perm_name => $perm_value) {
				$set_perms[] = array('usergroup_id' => $ugid, 'name' => $perm_name, 'value' => 1);
			}
		}

		foreach ($this->agent_perms as $aid => $perms) {
			foreach ($perms as $perm_name => $perm_value) {
				$has = false;
				if (isset($this->agent_to_agentgroups[$aid])) {
					foreach ($this->agent_to_agentgroups[$aid] as $ugid) {
						if (isset($this->agentgroup_perms[$ugid][$perm_name])) {
							$has = true;
							break;
						}
					}
				}

				if (!$has) {
					$set_perms[] = array('person_id' => $aid, 'name' => $perm_name, 'value' => 1);
				}
			}
		}

		return $set_perms;
	}
}