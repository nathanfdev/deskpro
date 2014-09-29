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
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionLoader;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Permission;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity;
use Orb\Util\Arrays;

/**
 * Loads general usergroup permissions likes flags and the like.
 */
class Usergroups extends AbstractLoader implements \Application\DeskPRO\People\PersonContextInterface
{
	/**
	 * @var array
	 */
	protected $perms = null;

	/**
	 * @var array
	 */
	protected $dynamic_perms = null;

	/**
	 * @var Person
	 */
	protected $person;

	/**
	 * @var int
	 */
	protected $person_id = 0;

	/**
	 * @var bool
	 */
	protected $with_overrides = false;


	/**
	 * @param Person $person
	 */
	public function setPersonContext(Person $person)
	{
		$this->person = $person;
		$this->person_id = $person->id;
	}

	public function getSubkey()
	{
		$this->getAllPermissions();
		if ($this->with_overrides && $this->person_id) {
			return 'person-' . $this->person->id;
		}
	}

	/**
	 * Get a permission value
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getPermission($name)
	{
		$this->getAllPermissions();

		switch ($name) {
			case 'core.tickets_submit_check':
				if ($this->getPermission('tickets.use')) {
					if (!$this->person->id && App::getSetting('core.interact_require_login')) {
						return 0;
					} else {
						return 1;
					}
				} else {
					return 0;
				}
				break;

			case 'core.feedback_submit_check':
				if ($this->getPermission('feedback.use')) {
					if (!$this->person->id && App::getSetting('core.interact_require_login')) {
						return 0;
					} else {
						return true;
					}
				} else {
					return 0;
				}
				break;
		}

		$val = false;
		if (isset($this->dynamic_perms[$name]) && $this->dynamic_perms[$name]) {
			$val = true;
		} else if (isset($this->perms[$name]) && $this->perms[$name]) {
			$val = true;
		}

		return $val;
	}


	/**
	 * Get an array of all effective permissions
	 *
	 * @return array
	 */
	public function getAllPermissions()
	{
		if ($this->perms === null) {
			if (!$this->usergroup_ids && !$this->person_id) {
				$this->perms = array();
			} else {
				if ($this->person_id) {
					$perms = App::getSystemService('PermissionsLoader')->getUsergroupPermissions($this->usergroup_ids);
					if ($this->person && $this->person->is_agent) {
						$overrides = App::getSystemService('PermissionsLoader')->getAgentOverridePermissions($this->person_id);
						if ($overrides) {
							$this->with_overrides = true;
							$perms = array_merge($perms, array(-1 => $overrides));
						}
					}
				} else {
					$perms = App::getSystemService('PermissionsLoader')->getUsergroupPermissions($this->usergroup_ids);
				}
				$perm_result = array();
				foreach ($perms as $p_group) {
					foreach ($p_group as $p) {
						$perm_result[] = $p;
					}
				}

				$this->perms = Permission::getEffectivePermissions($perm_result);
			}
		}

		if ($this->dynamic_perms === null) {
			$this->dynamic_perms = array();
			$agent_groups = App::$container->getAgentGroups();
			foreach ($this->usergroup_ids as $ugid) {
				if ($agent_groups->groupExists($ugid)) {
					$g = $agent_groups->getGroup($ugid);
					if ($g->sys_name == 'agent_all_perms' || $g->sys_name == 'agent_all_safe_perms') {
						$loader = App::$container->getSystemService('AgentPermissionNamesLoader');
						if ($g->sys_name == 'agent_all_perms') {
							$set_perms = $loader->getNames();
						} else {
							$set_perms = $loader->getSafeNames();
						}
						foreach ($set_perms as $n) {
							$this->dynamic_perms[$n] = true;
						}
					}
				}
			}
		}

		return $this->perms;
	}


	/**
	 * Get an array of data we'll serialize
	 *
	 * @return array
	 */
	protected function serializeData()
	{
		return array('perms' => $this->perms);
	}


	/**
	 * Initialize this object with an array of saved data
	 *
	 * @param array $data
	 */
	protected function unserializeData(array $data)
	{
		$this->perms = $data['perms'];
	}
}
