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

use Application\DeskPRO\Departments\Form\Type\TicketDepartmentType;
use Application\DeskPRO\Departments\TicketDepartmentEdit;
use Application\DeskPRO\Departments\TicketDepartmentEditor;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Settings\SettingHandler\TicketDepartment as TicketDepartmentHandler;
use Application\DeskPRO\Exception\ValidationException;
use Orb\Util\Arrays;

class TicketDepsController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		$data = array();

		$ticket_deps = $this->container->getSystemService('ticket_departments');
		$flat_array = $ticket_deps->getFlatArray();

		$deps = array();
		foreach ($flat_array as $row) {
			$deps[] = $row['object'];
		}

		$data['departments'] = $this->getApiData($deps, false);
		$data['default_id']  = $this->container->getSetting('core.default_ticket_dep');

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
		$data['perms_usergroup_ids']  = array();
		$data['perms_agentgroup_ids'] = array();
		$data['perms_agent_ids']      = array();

		foreach ($perms as $perm) {
			if ($perm['usergroup_id']) {
				if ($this->container->getDataService('Usergroup')->get($perm['usergroup_id'])->is_agent_group) {
					$data['perms_agentgroup_ids'][] = array(
						'usergroup_id' => (int)$perm['usergroup_id'],
						'perm_name'    => $perm['name'],
					);
				} else {
					$data['perms_usergroup_ids'][] = array(
						'usergroup_id' => (int)$perm['usergroup_id'],
						'perm_name'    => $perm['name'],
					);
				}
			} elseif ($perm['person_id']) {
				$data['perms_agent_ids'][] = array(
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

		$ticket_edit = new TicketDepartmentEdit($dep);

		$form = $this->createForm(
			new TicketDepartmentType(),
			$ticket_edit,
			array(
				'cascade_validation' => true
			)
		);

		$data = $this->in->getAll('post');
		$form->submit($data);

		if ($form->isValid()) {
			$ticket_edit->save($this->em);
			$ticket_edit->savePermissions(
				$this->em,
				$this->container->getAgentData()->getAgents(),
				$this->container->getDataService('Usergroup')->getAll()
			);
		}

		return $this->createApiResponse(array('id' => $dep->id, 'success' => true));
	}


	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction($id)
	{
		$editor = $this->_getDepartmentEditor();
		$dep = $editor->getDepartmentById($id);

		if (!$dep) {
			throw $this->createNotFoundException();
		}

		$move_to = $this->em->find('DeskPRO:Department', $this->in->getUint('move_to'));
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