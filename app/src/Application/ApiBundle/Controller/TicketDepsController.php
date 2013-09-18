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

use Application\DeskPRO\Departments\TicketDepartmentEditor;

class TicketDepsController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		$data = array();

		$deps = $this->em->createQuery("
			SELECT d
			FROM DeskPRO:Department d
			WHERE d.is_tickets_enabled = true
			ORDER BY d.display_order ASC
		")->execute();

		$data['departments'] = $this->getApiData($deps, false);
		$data['default_id']  = $this->container->getSetting('core.default_ticket_dep');

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		$dep = $this->em->find('DeskPRO:Department', $id);

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
		$editor = $this->_getDepartmentEditor($id);

		if ($this->in->checkIsset('properties')) {
			$editor->editProperties($this->in->getArrayValue('properties'));
		}

		if ($this->in->checkIsset('permissions')) {
			$editor->editPermissions(
				$this->in->checkIsset('permissions.agents')      ? $this->in->getCleanValueArray('permissions.agents') : null,
				$this->in->checkIsset('permissions.agentgroups') ? $this->in->getCleanValueArray('permissions.agentgroups') : null,
				$this->in->checkIsset('permissions.usergroups')  ? $this->in->getCleanValueArray('permissions.usergroups') : null
			);
		}

		return $this->createApiResponse(array('id' => $editor->getDepartment()->id, 'success' => true));
	}


	####################################################################################################################
	# remove
	####################################################################################################################

	public function removeAction($id)
	{
		$move_to = $this->in->getUint('move_to');
		$editor = $this->_getDepartmentEditor($id);

		$old_id = $editor->remove($move_to);

		return $this->createApiResponse(array('old_id' => $old_id, 'success' => true));
	}


	####################################################################################################################
	# save-display-order
	####################################################################################################################

	public function saveDisplayOrderAction()
	{
		$display_orders = $this->in->getCleanValueArray('display_orders', 'uint', 'discard');
		$editor = new TicketDepartmentEditor($this->em, null);
		$editor->updateDisplayOrders($display_orders);

		return $this->createSuccessResponse();
	}

	####################################################################################################################
	# get-settings
	####################################################################################################################

	public function getSettingsAction()
	{
		$settings = array(
			'core.default_ticket_dep'         => $this->container->getSetting('core.default_ticket_dep'),
			'core.phrase_department_singular' => $this->container->getSetting('core.phrase_department_singular'),
			'core.phrase_department_plural'   => $this->container->getSetting('core.phrase_department_plural'),
		);

		return $this->createApiResponse($settings);
	}

	####################################################################################################################
	# save-settings
	####################################################################################################################

	public function saveSettingsAction()
	{
		$set_settings = $this->in->getCleanValueArray('settings', 'string', 'string');

		if (isset($set_settings['core.default_ticket_dep']) && $set_settings['core.default_ticket_dep'] != $this->container->getSetting('core.default_ticket_dep')) {
			$this->container->getSettingsHandler()->setSetting('core.default_ticket_dep', $set_settings['core.default_ticket_dep']);
		}

		$change_phrase = array();
		if (isset($set_settings['core.phrase_department_singular']) && $set_settings['core.phrase_department_singular'] != $this->container->getSetting('core.phrase_department_singular')) {
			$change_phrase['singular'] = $set_settings['core.phrase_department_singular'];
		}
		if (isset($set_settings['core.phrase_department_plural']) && $set_settings['core.phrase_department_plural'] != $this->container->getSetting('core.phrase_department_plural')) {
			$change_phrase['plural'] = $set_settings['core.phrase_department_singular'];
		}

		if ($change_phrase) {
			if (!isset($change_phrase['singular'])) {
				$change_phrase['singular'] = $this->container->getSetting('core.phrase_department_singular');
			}
			if (!isset($change_phrase['plural'])) {
				$change_phrase['plural'] = $this->container->getSetting('core.phrase_department_plural');
			}

			$phrase_singular   = strtolower($change_phrase['singular']);
			$phrase_plural     = strtolower($change_phrase['plural']);
			$phrase_singular_c = ucwords($phrase_singular);
			$phrase_plural_c   = ucwords($phrase_plural);

			$groups_reader = new \Application\DeskPRO\ResourceScanner\LanguagePhrases();
			$phrases = $groups_reader->getAllUserPhrases();

			$batch = array();
			$ids = array();

			$d = date('Y-m-d H:i:s');

			foreach ($phrases as $phrase_id => $phrase_text) {
				$new_phrase = str_replace(
					array('departments', 'Departments', 'department', 'Department'),
					array($phrase_plural, $phrase_plural_c, $phrase_singular, $phrase_singular_c),
					$phrase_text
				);

				if ($new_phrase != $phrase_text) {
					$group = \Orb\Util\Strings::extractRegexMatch('#^(.*)\.([^.]+)$#', $phrase_id, 1);
					$batch[] = array(
						'language_id' => 1,
						'name'        => $phrase_id,
						'groupname'   => $group,
						'phrase'      => $new_phrase,
						'created_at'  => $d,
						'updated_at'  => $d
					);

					$ids[] = $phrase_id;
				}
			}

			if ($ids) {
				$this->db->beginTransaction();
				try {
					$this->db->executeQuery("
						DELETE FROM phrases
						WHERE name IN (" . $this->db->quoteIn($ids) . ") AND language_id = 1
					");

					$this->db->batchInsert('phrases', $batch);

					$this->db->commit();
				} catch (\Exception $e) {
					$this->db->rollback();
					throw $e;
				}
			}
		}

		return $this->createSuccessResponse();
	}


	####################################################################################################################


	/**
	 * @param $id
	 * @return TicketDepartmentEditor
	 * @throws
	 */
	private function _getDepartmentEditor($id)
	{
		if ($id) {
			$dep = $this->em->find('DeskPRO:Department', $id);

			if (!$dep || !$dep->is_tickets_enabled) {
				throw $this->createNotFoundException();
			}

			$editor = new TicketDepartmentEditor($this->em, $dep);
		} else {
			$editor = TicketDepartmentEditor::createNew($this->em);
		}


		return $editor;
	}
}