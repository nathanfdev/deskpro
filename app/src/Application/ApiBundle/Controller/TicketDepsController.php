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
use Application\DeskPRO\Departments\Form\Type\TicketDepartmentType;
use Application\DeskPRO\Departments\TicketDepartmentEdit;
use Application\DeskPRO\Departments\TicketDepartmentEditor;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\Settings\SettingHandler\TicketDepartment as TicketDepartmentHandler;

class TicketDepsController extends AbstractController implements ProtectedControllerInterface
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

		$ticket_deps = $this->container->getSystemService('ticket_departments');
		$flat_array = $ticket_deps->getFlatArray();

		$with_perms = $this->in->getBool('with_perms');
		if ($with_perms) {
			$perms = array();

			/** @var \Application\DeskPRO\DependencyInjection\SystemServices\UsergroupDataService $ug */
			$ug = $this->container->getDataService('Usergroup');

			$all_perms = $this->db->fetchAll("
				SELECT department_id, usergroup_id, person_id, name
				FROM department_permissions
				WHERE app = 'tickets'
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

		$deps_with_layout = $this->db->fetchAllCol("
			SELECT department_id
			FROM ticket_layouts
			WHERE department_id IS NOT NULL
		");

		if ($deps_with_layout) {
			$deps_with_layout = array_fill_keys($deps_with_layout, true);
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
			}

			if (isset($deps_with_layout[$r['id']])) {
				$r['has_layout'] = true;
			} else {
				$r['has_layout'] = false;
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
		$dep = $this->container->getSystemService('ticket_departments')->getById($id);

		if (!$dep || !$dep->is_tickets_enabled) {
			throw new $this->createNotFoundException();
		}

		$data = array();
		$data['department'] = $this->getApiData($dep);

		$perms = $this->db->fetchAll("SELECT usergroup_id, person_id, name FROM department_permissions WHERE department_id = ?", array($dep->id));

		$data['permissions'] = array(
			'usergroups'  => array(),
			'agentgroups' => array(),
			'agents'      => array()
		);

		foreach ($perms as $perm) {
			if ($perm['usergroup_id']) {
				if ($this->container->getDataService('Usergroup')->get($perm['usergroup_id'])->is_agent_group) {
					$data['permissions']['agentgroups'][] = array(
						'usergroup_id' => (int)$perm['usergroup_id'],
						'perm_name'    => $perm['name'],
					);
				} else {
					$data['permissions']['usergroups'][] = array(
						'usergroup_id' => (int)$perm['usergroup_id'],
						'perm_name'    => $perm['name'],
					);
				}
			} elseif ($perm['person_id']) {
				$data['permissions']['agents'][] = array(
					'agent_id'  => (int)$perm['person_id'],
					'perm_name' => $perm['name']
				);
			}
		}

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# save
	####################################################################################################################

	public function saveAction($id)
	{
		if ($id) {
			$dep = $this->container->getSystemService('ticket_departments')->getById($id);

			if (!$dep || !$dep->is_tickets_enabled) {
				throw new $this->createNotFoundException();
			}
		} else {
			$dep = Department::createTicketDepartment();
		}

		$dep_edit = new TicketDepartmentEdit($dep);

		$form = $this->createForm(
			new TicketDepartmentType(),
			$dep_edit,
			array(
				'cascade_validation' => true
			)
		);

		$data = $this->in->getAll('post');
		$form->submit($data, true);

		// todo need to sort out applying errors to the angualr view
		// todo handle "This form should not contain extra fields."

		if ($form->isValid() || 1) {
			$dep_edit->save($this->em);
			$dep_edit->savePermissions(
				$this->em,
				$this->container->getAgentData()->getAgents(),
				$this->container->getDataService('Usergroup')->getAll()
			);

			if (count($dep->children)) {
				//$dep_edit->clearTrigger($this->em);
			}

			return $this->createApiResponse(array('id' => $dep->id, 'success' => true));
		} else {
			$errors = array();

			foreach ($form->getErrors() as $er) {
				$errors[] = $er->getMessage();
			}

			return $this->createApiResponse(array('department_id' => $dep->id, 'success' => false, 'errors' => $errors));
		}
	}


	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction($id)
	{
		$editor = $this->_getDepartmentEditor();
		$dep = $this->container->getSystemService('ticket_departments')->getById($id);

		if (!$dep) {
			throw $this->createNotFoundException();
		}

		$move_to = $this->container->getSystemService('ticket_departments')->getById($this->in->getUint('move_to'));
		if (!$move_to) {
			throw ValidationException::create("department.remove.move_tickets", "You must select a department to move existing tickets into");
		}

		$old_id = $editor->removeDepartment($dep, $move_to);

		return $this->createApiResponse(array('old_id' => $old_id, 'success' => true));
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


	####################################################################################################################
	# get-settings
	####################################################################################################################

	public function getSettingsAction()
	{
		$settings = $this->_getDepartmentSettingHandler()->getSettings();
		return $this->createApiResponse($settings);
	}


	####################################################################################################################
	# save-settings
	####################################################################################################################

	public function saveSettingsAction()
	{
		$set_settings = $this->in->getCleanValueArray('settings', 'string', 'string');
		$this->_getDepartmentSettingHandler()->setSettings($set_settings);
		return $this->createSuccessResponse();
	}


	####################################################################################################################


	/**
	 * @param $id
	 * @return TicketDepartmentEditor
	 * @throws
	 */
	private function _getDepartmentEditor()
	{
		$editor = new TicketDepartmentEditor($this->em);
		return $editor;
	}


	/**
	 * @return TicketDepartmentHandler
	 */
	public function _getDepartmentSettingHandler()
	{
		$ticket_deps_settings = new TicketDepartmentHandler(
			$this->container->getSettingsHandler(),
			$this->db
		);

		return $ticket_deps_settings;
	}
}