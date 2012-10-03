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

use Application\DeskPRO\App;
use Application\DeskPRO\Searcher\OrganizationSearch;
use Application\DeskPRO\Entity\Organization;

class OrganizationController extends AbstractController
{
	public function searchAction()
	{
		$search_map = array(
			'address' => OrganizationSearch::TERM_CONTACT_ADDRESS,
			'im' => OrganizationSearch::TERM_CONTACT_IM,
			'label' => OrganizationSearch::TERM_LABEL,
			'name' => OrganizationSearch::TERM_NAME,
			'phone' => OrganizationSearch::TERM_CONTACT_PHONE,
		);

		$terms = array();

		foreach ($search_map AS $input => $search_key) {
			$value = $this->in->getCleanValueArray($input, 'raw', 'discard');
			if ($value) {
				$terms[] = array('type' => $search_key, 'op' => 'contains', 'options' => $value);
			}
		}

		foreach ($this->container->getSystemService('org_fields_manager')->getFields() as $field) {
			if ($this->in->checkIsset("field." . $field->getId())) {
				$in_val = $this->in->getString('field.'.$field->getId());
				if ($in_val) {
					$terms[] = array('type' => 'organization_field[' . $field->getId() . ']', 'op' => 'is', 'options' => array('value' => $in_val));
				}
			}
		}

		if ($this->in->checkIsset('order')) {
			$order_by = $this->in->getString('order');
		} else {
			$order_by = $this->person->getPref('agent.ui.org-filter-order-by.0');
			if (!$order_by) {
				$order_by = 'organization.name:asc';
			}
		}

		$extra = array();
		if ($order_by !== null) {
			$extra['order_by'] = $order_by;
		}

		if ($this->in->checkIsset('cache')) {
			$cache = $this->in->getUint('cache');
		} else {
			$cache = 3600;
		}

		$result_cache = $this->getApiSearchResult($terms, $extra, $cache, new OrganizationSearch());

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$per_page = 25;

		$person_ids = $result_cache->results;

		$page_ids = \Orb\Util\Arrays::getPageChunk($person_ids, $page, $per_page);
		$orgs = App::getEntityRepository('DeskPRO:Organization')->getByIds($page_ids, true);

		return $this->createApiResponse(array(
			'page' => $page,
			'per_page' => $per_page,
			'total' => count($person_ids),
			'organizations' => $this->getApiData($orgs)
		));
	}

	public function newOrganizationAction()
	{
		if (!$this->person->hasPerm('agent_org.create')) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$org = new Organization();
		$errors = array();

		$name = $this->in->getString('name');
		if (!$name) {
			$errors['name'] = array('required_field.name', 'name is empty or missing');
		}

		$org->name = $name;

		$bulk_set = array(
			'summary' => 'String',
		);
		foreach ($bulk_set AS $input => $type) {
			if ($this->in->checkIsset($input)) {
				$org->$input = $this->in->{'get' . $type}($input);
			}
		}

		if ($errors) {
			return $this->createApiMultipleErrorResponse($errors);
		}

		$this->db->beginTransaction();

		try {
			foreach ($this->in->getCleanValueArray('group_id', 'int') as $ug_id) {
				$ug = $this->em->find('DeskPRO:Usergroup', $ug_id);
				if ($ug && !$ug->is_agent_group && !$ug->sys_name) {
					$org->usergroups->add($ug);
				}
			}

			$this->em->persist($org);

			$field_manager = $this->container->getSystemService('org_fields_manager');
			$post_custom_fields = $this->getCustomFieldInput();
			if (!empty($post_custom_fields)) {
				$field_manager->saveFormToObject($post_custom_fields, $org, true);
			}
			$this->em->flush();

			$labels = $this->in->getCleanValueArray('label', 'string', 'discard');
			if ($labels) {
				$org->getLabelManager()->setLabelsArray($labels, $this->em);
				$this->em->flush();
			}

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createApiCreateResponse(
			array('id' => $org->id),
			$this->generateUrl('api_organizations_organization', array('organization_id' => $org->id), true)
		);
	}

	public function getOrganizationAction($organization_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		return $this->createApiResponse(array('organization' => $org->toApiData()));
	}

	public function postOrganizationAction($organization_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		if (!$this->person->hasPerm('agent_org.edit')) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$name = $this->in->getString('name');
		if ($name) {
			$org->name = $name;
		}

		$bulk_set = array(
			'summary' => 'String',
		);
		foreach ($bulk_set AS $input => $type) {
			if ($this->in->checkIsset($input)) {
				$org->$input = $this->in->{'get' . $type}($input);
			}
		}

		$this->db->beginTransaction();

		try {
			$this->em->persist($org);

			$field_manager = $this->container->getSystemService('org_fields_manager');
			$post_custom_fields = $this->getCustomFieldInput();
			if (!empty($post_custom_fields)) {
				$field_manager->saveFormToObject($post_custom_fields, $org, true);
			}
			$this->em->flush();

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createSuccessResponse();
	}

	public function deleteOrganizationAction($organization_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		if (!$this->person->hasPerm('agent_org.delete')) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$edit_manager = $this->container->getSystemService('org_edit_manager');
		$edit_manager->deleteOrganization($org);

		return $this->createSuccessResponse();
	}

	public function getOrganizationMembersAction($organization_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		$terms = array(
			array(
				'type' => \Application\DeskPRO\Searcher\PersonSearch::TERM_ORGANIZATION,
				'op' => 'contains',
				'options' => array($org->id)
			)
		);

		if ($this->in->checkIsset('order')) {
			$order_by = $this->in->getString('order');
		} else {
			$order_by = 'person.name:asc';
		}

		$extra = array();
		if ($order_by !== null) {
			$extra['order_by'] = $order_by;
		}

		if ($this->in->checkIsset('cache')) {
			$cache = $this->in->getUint('cache');
		} else {
			$cache = 3600;
		}

		$result_cache = $this->getApiSearchResult($terms, $extra, $cache, new \Application\DeskPRO\Searcher\PersonSearch());

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$per_page = 25;

		$person_ids = $result_cache->results;

		$page_ids = \Orb\Util\Arrays::getPageChunk($person_ids, $page, $per_page);
		$people = App::getEntityRepository('DeskPRO:Person')->getByIds($page_ids, true);

		return $this->createApiResponse(array(
			'page' => $page,
			'per_page' => $per_page,
			'total' => count($person_ids),
			'people' => $this->getApiData($people)
		));
	}

	public function getOrganizationTicketsAction($organization_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		$terms = array(
			array(
				'type' => \Application\DeskPRO\Searcher\TicketSearch::TERM_ORGANIZATION,
				'op' => 'contains',
				'options' => array($org->id)
			)
		);

		if ($this->in->checkIsset('order')) {
			$order_by = $this->in->getString('order');
		} else {
			$order_by = 'ticket.date_created:desc';
		}

		$extra = array();
		if ($order_by !== null) {
			$extra['order_by'] = $order_by;
		}

		if ($this->in->checkIsset('cache')) {
			$cache = $this->in->getUint('cache');
		} else {
			$cache = 3600;
		}

		$result_cache = $this->getApiSearchResult($terms, $extra, $cache, new \Application\DeskPRO\Searcher\TicketSearch());

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		$per_page = 25;

		$person_ids = $result_cache->results;

		$page_ids = \Orb\Util\Arrays::getPageChunk($person_ids, $page, $per_page);
		$tickets = App::getEntityRepository('DeskPRO:Ticket')->getByIds($page_ids, true);

		return $this->createApiResponse(array(
			'page' => $page,
			'per_page' => $per_page,
			'total' => count($person_ids),
			'tickets' => $this->getApiData($tickets)
		));
	}

	public function getOrganizationNotesAction($organization_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		$notes = $this->em->getRepository('DeskPRO:OrganizationNote')->getNotesForOrganization($org);

		return $this->createApiResponse(array('notes' => $this->getApiData($notes)));
	}

	public function postOrganizationNotesAction($organization_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		if (!$this->person->hasPerm('agent_org.note')) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$note_text = $this->in->getString('note');
		if (!$note_text) {
			return $this->createApiErrorResponse('required_field', 'note field is empty or missing');
		}

		$note = new \Application\DeskPRO\Entity\OrganizationNote();
		$note['agent'] = $this->person;
		$note['organization'] = $org;
		$note['note'] = $note_text;

		$this->em->persist($note);
		$this->em->flush();

		return $this->createApiCreateResponse(
			array('id' => $note->id),
			$this->generateUrl('api_organizations_organization_notes_note', array('organization_id' => $org->id, 'note_id' => $note->id), true)
		);
	}

	public function getOrganizationNoteAction($organization_id, $note_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		$note = $this->em->getRepository('DeskPRO:OrganizationNote')->find($note_id);
		if (!$note || $note->organization->id != $org->id) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		return $this->createApiResponse(array('note' => $note->toApiData()));
	}

	public function getOrganizationContactDetailsAction($organization_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		return $this->createApiResponse(array('details' => $this->getApiData($org->contact_data)));
	}

	public function getOrganizationContactDetailAction($organization_id, $contact_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		foreach ($org->contact_data AS $contact) {
			if ($contact->id == $contact_id) {
				return $this->createApiResponse(array('exists' => true));
			}
		}

		return $this->createApiResponse(array('exists' => false));
	}

	public function deleteOrganizationContactDetailAction($organization_id, $contact_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		foreach ($org->contact_data AS $key => $contact) {
			if ($contact->id == $contact_id) {
				unset($org->contact_data[$key]);
				$this->em->persist($org);
				$this->em->flush();
				break;
			}
		}

		return $this->createSuccessResponse();
	}

	public function getOrganizationGroupsAction($organization_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		return $this->createApiResponse(array('groups' => $this->getApiData($org->usergroups)));
	}

	public function postOrganizationGroupsAction($organization_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		$group_id = $this->in->getUint('id');

		$match = $this->db->fetchColumn('
			SELECT id
			FROM usergroups
			WHERE id = ?
				AND sys_name IS NULL
				AND is_agent_group = 0
		', array($group_id));
		if (!$match) {
			return $this->createApiErrorResponse('required_field', 'id must be specified as a non-system group');
		}

		$exists = false;
		foreach ($org->usergroups AS $group) {
			if ($group->id == $group_id) {
				$exists = true;
			}
		}

		if (!$exists) {
			$this->db->insert('organization2usergroups', array(
				'organization_id' => $org->id,
				'usergroup_id' => $group_id
			));
		}

		return $this->createApiCreateResponse(
			array('id' => $group_id),
			$this->generateUrl('api_organizations_organization_group', array('organization_id' => $org->id, 'usergroup_id' => $group_id), true)
		);
	}

	public function getOrganizationGroupAction($organization_id, $usergroup_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		foreach ($org->usergroups AS $group) {
			if ($group->id == $usergroup_id) {
				return $this->createApiResponse(array('exists' => true));
			}
		}

		return $this->createApiResponse(array('exists' => false));
	}

	public function deleteOrganizationGroupAction($organization_id, $usergroup_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		foreach ($org->usergroups AS $key => $group) {
			if ($group->id == $usergroup_id) {
				if ($group->is_agent_group) {
					return $this->createApiErrorResponse('invalid_group', 'Group is an agent group');
				}
				unset($org->usergroups[$key]);
				$this->em->persist($org);
				$this->em->flush();
				break;
			}
		}

		return $this->createSuccessResponse();
	}

	public function getOrganizationLabelsAction($organization_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		return $this->createApiResponse(array('labels' => $this->getApiData($org->labels)));
	}

	public function postOrganizationLabelsAction($organization_id)
	{
		$org = $this->_getOrganizationOr404($organization_id);
		$label = $this->in->getString('label');

		if ($label === '') {
			return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
		}

		$org->getLabelManager()->addLabel($label);
		$this->em->persist($org);
		$this->em->flush();

		return $this->createApiCreateResponse(
			array('label' => $label),
			$this->generateUrl('api_organizations_organization_label', array('organization_id' => $org->id, 'label' => $label), true)
		);
	}

	public function getOrganizationLabelAction($organization_id, $label)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		if ($org->getLabelManager()->hasLabel($label)) {
			return $this->createApiResponse(array('exists' => true));
		} else {
			return $this->createApiResponse(array('exists' => false));
		}
	}

	public function deleteOrganizationLabelAction($organization_id, $label)
	{
		$org = $this->_getOrganizationOr404($organization_id);

		$org->getLabelManager()->removeLabel($label);
		$this->em->persist($org);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	public function getFieldsAction()
	{
		$field_manager = $this->container->getSystemService('org_fields_manager');
		$fields = $field_manager->getFields();

		return $this->createApiResponse(array('fields' => $this->getApiData($fields)));
	}

	public function getGroupsAction()
	{
		$groups = $this->em->createQuery('
			SELECT g
			FROM DeskPRO:Usergroup g INDEX BY g.id
			WHERE g.is_agent_group = false AND g.sys_name IS NULL
			ORDER BY g.id
		')->execute();

		return $this->createApiResponse(array('groups' => $this->getApiData($groups)));
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\Organization
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function _getOrganizationOr404($id)
	{
		$org = $this->em->getRepository('DeskPRO:Organization')->findOneById($id);

		if (!$org) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no organization with ID $id");
		}

		return $org;
	}
}
