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

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\People\AgentPermissions\GroupsDbLoader;
use Application\DeskPRO\People\AgentPermissions\PersonDbLoader as AgentPermsPersonDbLoader;

/**
* @SWG\Resource(
* 	resourcePath="/tasks",
* 	description="Operations about Tasks",
* 	basePath="/api/tasks"
* )
*/

class TasksController extends AbstractController implements ProtectedControllerInterface
{
	const KEY_ENABLED = 'core.apps_tasks';
	const KEY_REMINDER = 'task_reminder_time';

	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}

	/**
	 * get tasks settings
	 *
	 * @return Response
	 */
	public function settingsAction()
	{
		$agents = array();

		foreach ($this->container->getAgentData()->getAgents() as $agent) {
			$agent_data = $agent->toApiData();
			$perm_loader = new AgentPermsPersonDbLoader($agent, $this->em);
			$agent_data['perms'] = $perm_loader->getEffectivePermissions()->toArray();
			$agents[] = $agent_data;
		}

		$ugs = $this->em->createQuery("
				SELECT ug
				FROM DeskPRO:Usergroup ug
				WHERE ug.is_agent_group = true
				ORDER BY ug.title ASC
			")->execute();

		$groups = $this->getApiData($ugs);
		$ids = array_map(function($g){ return $g['id']; }, $groups);

		$loader = new GroupsDbLoader($ids, $this->em);
		foreach ($groups as &$group) {
			$group['perms'] = $loader->getGroupPermissions($group['id']);
		}

		return $this->createApiResponse(array(
			'enabled' => $this->settings->get(self::KEY_ENABLED, 0),
			self::KEY_REMINDER => $this->settings->get(self::KEY_REMINDER, '09:00'),
			'agents' => $agents,
			'groups' => $groups,
		));
	}

	/**
	 * update tasks settings
	 */
	public function updateSettingsAction()
	{
		$this->settings->setSetting(self::KEY_ENABLED, $this->in->getUInt('enabled'));
		$this->settings->setSetting(self::KEY_REMINDER, $this->in->getString(self::KEY_REMINDER));
		return $this->settingsAction();
	}
}