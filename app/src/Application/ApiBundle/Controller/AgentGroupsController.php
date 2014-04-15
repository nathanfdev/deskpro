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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\ApiBundle\PermissionStrategy\MultiPermissions;
use Application\ApiBundle\PermissionStrategy\PassPermission;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\People\AgentPermissions\AgentPermissions;
use Application\DeskPRO\People\AgentPermissions\GroupDbPersister;
use Application\DeskPRO\People\AgentPermissions\GroupsDbLoader;
use Orb\Util\Arrays;

class AgentGroupsController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		$multi = new MultiPermissions();
		$multi->addPermissionStrategy(new AdminManagePermission());
		$multi->addPermissionStrategy(new PassPermission(), 'listAction');
		return $multi;
	}


	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		$ugs = $this->em->createQuery("
				SELECT ug
				FROM DeskPRO:Usergroup ug
				WHERE ug.is_agent_group = true
				ORDER BY ug.title ASC
			")->execute();

		$data['groups'] = $this->getApiData($ugs);

		return $this->createApiResponse($data);
	}


	###################################################################################################################
	# get
	####################################################################################################################

	public function getGroupAction($id)
	{
		$group = $this->em->find('DeskPRO:Usergroup', $id);

		if (!$group || !$group->is_agent_group) {
			throw $this->createNotFoundException();
		}

		$loader = new GroupsDbLoader(array($group), $this->em);

		$data = $group->toApiData();
		$data['members'] = array();
		$data['perms']   = $loader->getGroupPermissions($group->id);

		$member_ids = $this->db->fetchAllCol("SELECT person_id FROM person2usergroups WHERE usergroup_id = ?", array($group->id));
		if ($member_ids) {
			foreach ($member_ids as $pid) {
				$agent = $this->container->getAgentData()->get($pid);
				if ($agent) {
					$data['members'][] = $agent->toApiData(false, false);
				}
			}
		}

		return $this->createApiResponse(array('group' => $data));
	}


	####################################################################################################################
	# save-group
	####################################################################################################################

	public function saveGroupAction($id)
	{
		if ($id) {
			$is_new = false;
			$group = $this->em->find('DeskPRO:Usergroup', $id);

			if (!$group || !$group->is_agent_group) {
				throw $this->createNotFoundException();
			}
		} else {
			$is_new = true;
			$group = new Usergroup();
			$group->is_agent_group = true;
			$group->is_enabled = true;
		}

		$group->title = $this->in->getString('group.title');

		#------------------------------
		# Save team
		#------------------------------

		$errors = $this->container->getValidator()->validate($group);
		if (count($errors)) {
			return $this->createApiValidationErrorResponse($errors);
		}

		$this->em->persist($group);
		$this->em->flush();

		#------------------------------
		# Save perms
		#------------------------------

		$perms = new AgentPermissions();
		$perms->fromArray($this->in->getArrayValue('group.perms'));

		$persister = new GroupDbPersister($this->em);
		$persister->savePerms($group, $perms);

		#------------------------------
		# Save members
		#------------------------------

		if ($is_new) {
			$current_members = array();
		} else {
			$current_members = $this->db->fetchAllCol("SELECT person_id FROM person2usergroups WHERE usergroup_id = ?", array($group->id));
		}

		$new_members = $this->in->getArrayOfUInts('group.person_ids');
		$new_members = array_unique($new_members);
		$new_members = Arrays::removeFalsey($new_members);
		if ($new_members) {
			$agent_data = $this->container->getAgentData();
			$new_members = array_filter($new_members, function($a) use ($agent_data) {
				return $agent_data->get($a) ? true : false;
			});
		}

		$del_members = array_diff($current_members, $new_members);
		$new_members = array_diff($new_members, $current_members);

		if (!$is_new && $del_members) {
			$this->db->deleteIn('person2usergroups', $del_members, 'person_id', "group_id = {$group->id}");
		}
		if ($new_members) {
			$ins = array();
			foreach ($new_members as $pid) {
				$ins[] = array('usergroup_id' => $group->id, 'person_id' => $pid);
			}
			$this->db->batchInsert('person2usergroups', $ins, true);
		}

		#------------------------------
		# Save department perms
		#------------------------------

		if ($this->in->checkIsset('dep_perms')) {
			$ticket_deps = $this->container->getTicketDepartments();
			$chat_deps   = $this->container->getChatDepartments();

			$set_perms = array();
			foreach ($this->in->getArrayValue('dep_perms.tickets') as $did => $p) {
				if (!$ticket_deps->getById($did)) continue;
				if ($p['full']) {
					$set_perms[] = array('department_id' => $did, 'usergroup_id' => $group->id, 'app' => 'tickets', 'name' => 'full', 'value' => 1);
				} else if ($p['assign']) {
					$set_perms[] = array('department_id' => $did, 'usergroup_id' => $group->id, 'app' => 'tickets', 'name' => 'assign', 'value' => 1);
				}
			}
			foreach ($this->in->getArrayValue('dep_perms.chat') as $did => $p) {
				if (!$chat_deps->getById($did)) continue;
				if ($p['full']) {
					$set_perms[] = array('department_id' => $did, 'usergroup_id' => $group->id, 'app' => 'chat', 'name' => 'full', 'value' => 1);
				}
			}

			$this->db->executeUpdate("DELETE FROM department_permissions WHERE usergroup_id = ?", array($group->id));
			if ($set_perms) {
				$this->db->batchInsert('department_permissions', $set_perms, true);
			}
		}

		#------------------------------
		# Clear permission cache
		#------------------------------

		$this->db->executeUpdate("DELETE FROM permissions_cache");

		#------------------------------
		# Return
		#------------------------------

		if ($is_new) {
			return $this->createApiCreateResponse(
				array('group_id' => $group->id),
				$this->generateUrl('api_agentgroups_get', array('id' => $group->id))
			);
		} else {
			return $this->createApiSuccessResponse(array(
				'group_id' => $group->id
			));
		}
	}


	####################################################################################################################
	# delete-group
	####################################################################################################################

	public function deleteGroupAction($id)
	{
		$group = $this->em->find('DeskPRO:Usergroup', $id);

		if (!$group || !$group->is_agent_group) {
			throw $this->createNotFoundException();
		}

		$old_id = $group->id;
		$this->em->remove($group);
		$this->em->flush();

		$this->createApiDeleteResponse(array('old_group_id' => $old_id));
	}


	####################################################################################################################
	# get-all-perms
	####################################################################################################################

	public function getAllPermsAction()
	{
		$ugs = $this->em->createQuery("
			SELECT ug
			FROM DeskPRO:Usergroup ug
			WHERE ug.is_agent_group = true
			ORDER BY ug.title ASC
		")->execute();

		$loader = new GroupsDbLoader($ugs, $this->em);

		$group_data = array();
		foreach ($ugs as $ug) {
			$group_data[] = array(
				'group' => array('id' => $ug->id, 'title' => $ug->title),
				'perms' => $loader->getGroupPermissions($ug->id)->toArray(),
			);
		}
		return $this->createApiResponse(array('groups' => $group_data));
	}


	####################################################################################################################
	# toggle-group
	####################################################################################################################

	public function toggleGroupAction($id, $is_enabled)
	{
		$group = $this->em->find('DeskPRO:Usergroup', $id);

		if (!$group || !$group->is_agent_group) {
			throw $this->createNotFoundException();
		}

		$group->is_enabled = (bool)$is_enabled;
		$this->em->persist($group);
		$this->em->flush();

		return $this->createSuccessResponse();
	}
}