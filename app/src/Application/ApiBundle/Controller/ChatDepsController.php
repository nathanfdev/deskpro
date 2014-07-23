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
use Application\DeskPRO\Departments\ChatDepartmentEdit;
use Application\DeskPRO\Departments\ChatDepartmentEditor;
use Application\DeskPRO\Departments\Form\Type\ChatDepartmentType;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Exception\ValidationException;

class ChatDepsController extends AbstractController implements ProtectedControllerInterface
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
		$data = array();

		$chat_deps   = $this->container->getSystemService('chat_departments');
		$flat_array  = $chat_deps->getFlatArray();

		$ag = $this->container->getAgentGroups();
		$with_perms = $this->in->getBool('with_perms');

		if ($with_perms) {
			$perms = array();

			/** @var \Application\DeskPRO\DependencyInjection\SystemServices\UsergroupDataService $ug */
			$ug = $this->container->getDataService('Usergroup');

			$all_perms = $this->db->fetchAll("
				SELECT department_id, usergroup_id, person_id, name
				FROM department_permissions
				WHERE app = 'chat'
			");

			foreach ($all_perms as $p) {
				if (!isset($perms[$p['department_id']])) {
					$perms[$p['department_id']] = array('agentgroups' => array(), 'usergroups' => array(), 'users' => array());
				}

				if ($p['usergroup_id']) {
					if ($ug->getAgentGroup($p['usergroup_id'])) {
						$perms[$p['department_id']]['agentgroups'][] = array('id' => (int)$p['usergroup_id'], 'name' => $p['name']);
					} else {
						$perms[$p['department_id']]['usergroups'][] = array('id' => (int)$p['usergroup_id'], 'name' => $p['name']);
					}
				} else {
					$perms[$p['department_id']]['users'][] = array('id' => (int)$p['person_id'], 'name' => $p['name']);
				}
			}
		}

		$deps = array();

		foreach ($flat_array as $row) {
			$r = $row['object']->toApiData(true, false);
			$r['depth'] = $row['depth'];

			if ($with_perms) {
				if (isset($perms[$r['id']])) {
					$r['permissions'] = $perms[$r['id']];
				} else {
					$r['permissions'] = array();
				}

				if (!isset($r['permissions']['agentgroups'])) {
					$r['permissions']['agentgroups'] = array();
				}
				$r['permissions']['agentgroups'][] = array('id' => $ag->getSysGroup('agent_all_perms')->id, 'name' => 'full');
				$r['permissions']['agentgroups'][] = array('id' => $ag->getSysGroup('agent_all_safe_perms')->id, 'name' => 'full');
			}

			$deps[] = $r;
		}

		$data['departments'] = $deps;

		return $this->createApiResponse($data);
	}

	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		/**
		 * @var \Application\DeskPRO\Departments\ChatDepartments $chat_deps
		 */

		$chat_deps = $this->container->getSystemService('chat_departments');
		$dep       = $chat_deps->getById($id);

		if (!$dep || !$dep->is_chat_enabled) {

			throw $this->createNotFoundException();
		}

		$data                = array();
		$data['department']  = $this->getApiData($dep);
		$data['permissions'] = $chat_deps->getPermissionsInfo($dep);

		$ag = $this->container->getAgentGroups();
		$data['permissions']['agentgroups'][] = array('usergroup_id' => $ag->getSysGroup('agent_all_perms')->id, 'perm_name' => 'full');
		$data['permissions']['agentgroups'][] = array('usergroup_id' => $ag->getSysGroup('agent_all_safe_perms')->id, 'perm_name' => 'full');

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		if ($id) {

			/**
			 * @var \Application\DeskPRO\Departments\ChatDepartments $chat_deps
			 */

			$chat_deps = $this->container->getSystemService('chat_departments');
			$dep       = $chat_deps->getById($id);

			if (!$dep || !$dep->is_chat_enabled) {

				throw $this->createNotFoundException();
			}
		} else {

			$dep = Department::createChatDepartment();
		}

		$postData = $this->in->getAll('post');

		$chat_edit = new ChatDepartmentEdit($dep);

		$form = $this->createForm(new ChatDepartmentType(), $chat_edit, array('cascade_validation' => true));

		$form->submit($this->deleteExtraDataFromRequest($form, $postData, array('department', 'permissions')), true);

		if ($form->isValid()) {

			$chat_edit->save($this->em);

			$chat_edit->savePermissions(
				$this->em,
				$this->container->getAgentData()->getAgents(),
				$this->container->getDataService('Usergroup')->getAll()
			);

		} else {

			throw ValidationException::create($this->getFormValidationErrorsString($form));
		}

		return $this->createApiResponse(
			array(
				 'success' => true,
				 'id'      => $dep->id,
			)
		);
	}

	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction($id)
	{
		/**
		 * @var \Application\DeskPRO\Departments\ChatDepartments $chat_deps
		 */

		$chat_deps = $this->container->getSystemService('chat_departments');
		$editor    = $this->_getDepartmentEditor();
		$dep       = $chat_deps->getById($id);

		if (!$dep) {

			throw $this->createNotFoundException();
		}

		$move_to_dep = $chat_deps->getById($this->in->getUint('move_to'));

		if (!$move_to_dep) {

			throw ValidationException::create("department.move_chat.dep_not_valid");
		}

		$old_id = $editor->removeDepartment($dep, $move_to_dep);

		return $this->createSuccessResponse(array('old_id' => $old_id));
	}

	####################################################################################################################
	# save-display-order
	####################################################################################################################

	public function saveDisplayOrderAction()
	{
		$display_orders = $this->in->getArrayOfUInts('display_orders');

		$editor = $this->_getDepartmentEditor();
		$editor->updateDisplayOrders($display_orders);

		return $this->createSuccessResponse();
	}

	/**
	 * @return ChatDepartmentEditor
	 */

	private function _getDepartmentEditor()
	{
		$editor = new ChatDepartmentEditor($this->em);
		return $editor;
	}
}