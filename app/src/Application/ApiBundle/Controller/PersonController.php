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
use Application\DeskPRO\Searcher\PersonSearch;
use Application\DeskPRO\Entity\Person;

class PersonController extends AbstractController
{
	public function searchAction()
	{
		$search_map = array(
			'address' => PersonSearch::TERM_CONTACT_ADDRESS,
			'agent_team_id' => PersonSearch::TERM_AGENT_TEAM,
			'alpha' => PersonSearch::TERM_ALPHA,
			'email' => PersonSearch::TERM_EMAIL,
			'email_domain' => PersonSearch::TERM_EMAIL_DOMAIN,
			'im' => PersonSearch::TERM_CONTACT_IM,
			'is_agent_confirmed' => PersonSearch::TERM_IS_AGENT_CONFIRMED,
			'label' => PersonSearch::TERM_LABEL,
			'name' => PersonSearch::TERM_NAME,
			'language_id' => PersonSearch::TERM_LANGUAGE,
			'organization_id' => PersonSearch::TERM_ORGANIZATION,
			'phone' => PersonSearch::TERM_CONTACT_PHONE,
			'usergroup_id' => PersonSearch::TERM_USERGROUP,
		);

		$terms = array();

		foreach ($search_map AS $input => $search_key) {
			$value = $this->in->getCleanValueArray($input, 'raw', 'discard');
			if ($value) {
				$terms[] = array('type' => $search_key, 'op' => 'contains', 'options' => $value);
			}
		}

		if ($this->in->getBool('is_agent')) {
			$terms[] = array('type' => PersonSearch::TERM_AGENT_MODE, 'op' => 'contains', 'options' => 1);
		}

		$date_created_start = $this->in->getUint('date_created_start');
		$date_created_end = $this->in->getUint('date_created_end');
		if ($date_created_end) {
			$terms[] = array('type' => PersonSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => array(
				'date1' => $date_created_start,
				'date2' => $date_created_end
			));
		} else if ($date_created_start) {
			$terms[] = array('type' => PersonSearch::TERM_DATE_CREATED, 'op' => 'between', 'options' => array(
				'date1' => $date_created_start
			));
		}

		foreach ($this->container->getSystemService('person_fields_manager')->getFields() as $field) {
			if ($this->in->checkIsset("field." . $field->getId())) {
				$in_val = $this->in->getString('field.'.$field->getId());
				if ($in_val) {
					$terms[] = array('type' => 'person_field[' . $field->getId() . ']', 'op' => 'is', 'options' => array('value' => $in_val));
				}
			}
		}

		if ($this->in->checkIsset('order')) {
			$order_by = $this->in->getString('order');
		} else {
			$order_by = $this->person->getPref('agent.ui.people-filter-order-by.0');
			if (!$order_by) {
				$order_by = 'people.id:asc';
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

		$result_cache = $this->getApiSearchResult($terms, $extra, $cache, new PersonSearch());

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

	public function newPersonAction()
	{
		if (!$this->person->hasPerm('agent_people.create')) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$person = new Person();
		$errors = array();

		$name = $this->in->getString('name');
		if (!$name) {
			$errors['name'] = array('required_field.name', 'name is empty or missing');
		}
		else {
			$person->name = $name;
		}

		$email = $this->in->getString('email');
		if (!$email || !\Orb\Validator\StringEmail::isValueValid($email)) {
			$errors['email'] = array('required_field.email', 'email is empty or invalid');
		} else {
			$check_exists = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);
			if ($check_exists) {
				$errors['email'] = array('invalid_argument.email', 'email already exists');
			} else {
				$person->setEmail($email);
			}
		}

		$updates = $this->_setBasicPersonDetailsFromInput($person);

		if ($errors) {
			return $this->createApiMultipleErrorResponse($errors);
		}

		$this->db->beginTransaction();

		try {
			foreach ($this->in->getCleanValueArray('group_id', 'int') as $ug_id) {
				$ug = $this->em->find('DeskPRO:Usergroup', $ug_id);
				if ($ug && !$ug->is_agent_group && !$ug->sys_name) {
					$person->usergroups->add($ug);
				}
			}

			if ($updates['new_org']) {
				$this->em->persist($updates['new_org']);
			}

			$this->em->persist($person);

			$field_manager = $this->container->getSystemService('person_fields_manager');
			$post_custom_fields = $this->getCustomFieldInput();
			if (!empty($post_custom_fields)) {
				$field_manager->saveFormToObject($post_custom_fields, $person, true);
			}
			$this->em->flush();

			$labels = $this->in->getCleanValueArray('label', 'string', 'discard');
			if ($labels) {
				$person->getLabelManager()->setLabelsArray($labels, $this->em);
				$this->em->flush();
			}

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createApiCreateResponse(
			array('id' => $person->id),
			$this->generateUrl('api_people_person', array('person_id' => $person->id), true)
		);
	}

	public function getPersonAction($person_id)
	{
		$person = $this->_getPersonOr404($person_id);

		return $this->createApiResponse(array('person' => $person->toApiData()));
	}

	public function postPersonAction($person_id)
	{
		$person = $this->_getPersonOr404($person_id, 'edit');

		$errors = array();

		$name = $this->in->getString('name');
		if ($name) {
			$person->name = $name;
		}

		$updates = $this->_setBasicPersonDetailsFromInput($person);

		if ($this->in->checkIsset('primary_email') && $this->person->hasPerm('agent_people.manage_emails')) {
			$email = $this->in->getString('primary_email');

			$check_exists = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);
			if ($check_exists) {
				if ($check_exists->id != $person->id) {
					$errors['primary_email'] = array('invalid_argument.primary_email', 'email already exists');
				}
			} else {
				$person->setEmail($email);
			}
		}

		if ($errors) {
			return $this->createApiMultipleErrorResponse($errors);
		}

		$this->db->beginTransaction();

		try {
			if ($updates['new_org']) {
				$this->em->persist($updates['new_org']);
			}
			$this->em->persist($person);

			$field_manager = $this->container->getSystemService('person_fields_manager');
			$post_custom_fields = $this->getCustomFieldInput();
			if (!empty($post_custom_fields)) {
				$field_manager->saveFormToObject($post_custom_fields, $person, true);
			}
			$this->em->flush();

			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		return $this->createSuccessResponse();
	}

	protected function _setBasicPersonDetailsFromInput(Person $person)
	{
		$org = null;

		if ($this->in->checkIsset('organization')) {
			$organization = $this->in->getString('organization');
			if ($organization !== '') {
				$org = $this->em->getRepository('DeskPRO:Organization')->getByName($organization);

				if (!$org) {
					$org = new Organization();
					$org->name = $organization;
				}

				$person->organization = $org;
			} else {
				$person->organization = null;
				$person->organization_position = '';
			}
		} else if ($this->in->checkIsset('organization_id')) {
			$organization_id = $this->in->getUint('organization_id');
			if ($organization_id) {
				$org = $this->em->getRepository('DeskPRO:Organization')->find($organization_id);
			}

			if ($org) {
				$person->organization = $org;
			} else {
				$person->organization = null;
				$person->organization_position = '';
			}
		}

		if ($this->in->checkIsset('organization_position') && $person->organization) {
			$person->organization_position = $this->in->getString('organization_position');
		}

		if ($this->in->checkIsset('timezone') && in_array($this->in->getString('timezone'), \DateTimeZone::listIdentifiers())) {
			$person->timezone = $this->in->getString('timezone');
		}

		$bulk_set = array(
			'summary' => 'String',
			'disable_autoresponses' => 'Bool'
		);
		foreach ($bulk_set AS $input => $type) {
			if ($this->in->checkIsset($input)) {
				$person->$input = $this->in->{'get' . $type}($input);
			}
		}

		return array(
			'new_org' => $org
		);
	}

	public function deletePersonAction($person_id)
	{
		$person = $this->_getPersonOr404($person_id, 'delete');

		$edit_manager = $this->container->getSystemService('person_edit_manager');
		$edit_manager->setPersonContext($this->person);
		$edit_manager->deleteUser($person);

		return $this->createSuccessResponse();
	}

	public function getPersonTicketsAction($person_id)
	{
		$person = $this->_getPersonOr404($person_id);

		$terms = array(
			array(
				'type' => \Application\DeskPRO\Searcher\TicketSearch::TERM_PERSON,
				'op' => 'contains',
				'options' => array($person->id)
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

	public function resetPasswordAction($person_id)
	{
		$person = $this->_getPersonOr404($person_id, 'reset_password');

		$password = $this->in->getString('password');
		if (!$password) {
			return $this->createApiErrorResponse('required_field', 'password field is missing or empty');
		}

		if ($this->in->checkIsset('send_email')) {
			$send_email = $this->in->getBool('send_email');
		} else {
			$send_email = true;
		}

		$person->setPassword($password);
		$this->em->persist($person);

		if ($send_email) {
			$message = $this->container->getMailer()->createMessage();
			$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
			$message->setTemplate('DeskPRO:emails_user:agent-changed-password.html.twig', array(
				'person' => $person
			));
			$message->enableQueueHint();
			$this->container->getMailer()->send($message);
		}

		return $this->createSuccessResponse();
	}

	public function getPersonNotesAction($person_id)
	{
		$person = $this->_getPersonOr404($person_id);
		$notes = $this->em->getRepository('DeskPRO:PersonNote')->getNotesForPerson($person);

		return $this->createApiResponse(array('notes' => $this->getApiData($notes)));
	}

	public function postPersonNotesAction($person_id)
	{
		$person = $this->_getPersonOr404($person_id, 'notes');

		$note_text = $this->in->getString('note');
		if (!$note_text) {
			return $this->createApiErrorResponse('required_field', 'note field is empty or missing');
		}

		$note = new \Application\DeskPRO\Entity\PersonNote();
		$note['agent'] = $this->person;
		$note['person'] = $person;
		$note['note'] = $note_text;

		$this->em->persist($note);
		$this->em->flush();

		return $this->createApiCreateResponse(
			array('id' => $note->id),
			$this->generateUrl('api_people_person_notes_note', array('person_id' => $person->id, 'note_id' => $note->id), true)
		);
	}

	public function getPersonContactDetailsAction($person_id)
	{
		$person = $this->_getPersonOr404($person_id);

		return $this->createApiResponse(array('details' => $this->getApiData($person->contact_data)));
	}

	public function getPersonContactDetailAction($person_id, $contact_id)
	{
		$person = $this->_getPersonOr404($person_id);

		foreach ($person->contact_data AS $contact) {
			if ($contact->id == $contact_id) {
				return $this->createApiResponse(array('exists' => true));
			}
		}

		return $this->createApiResponse(array('exists' => false));
	}

	public function deletePersonContactDetailAction($person_id, $contact_id)
	{
		$person = $this->_getPersonOr404($person_id, 'edit');

		foreach ($person->contact_data AS $key => $contact) {
			if ($contact->id == $contact_id) {
				unset($person->contact_data[$key]);
				$this->em->persist($person);
				$this->em->flush();
				break;
			}
		}

		return $this->createSuccessResponse();
	}

	public function getPersonGroupsAction($person_id)
	{
		$person = $this->_getPersonOr404($person_id);

		return $this->createApiResponse(array('groups' => $this->getApiData($person->usergroups)));
	}

	public function postPersonGroupsAction($person_id)
	{
		$person = $this->_getPersonOr404($person_id, 'edit');

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
		foreach ($person->usergroups AS $group) {
			if ($group->id == $group_id) {
				$exists = true;
			}
		}

		if (!$exists) {
			$this->db->insert('person2usergroups', array(
				'person_id' => $person->id,
				'usergroup_id' => $group_id
			));
		}

		return $this->createApiCreateResponse(
			array('id' => $group_id),
			$this->generateUrl('api_people_person_group', array('person_id' => $person->id, 'usergroup_id' => $group_id), true)
		);
	}

	public function getPersonGroupAction($person_id, $usergroup_id)
	{
		$person = $this->_getPersonOr404($person_id);

		foreach ($person->usergroups AS $group) {
			if ($group->id == $usergroup_id) {
				return $this->createApiResponse(array('exists' => true));
			}
		}

		return $this->createApiResponse(array('exists' => false));
	}

	public function deletePersonGroupAction($person_id, $usergroup_id)
	{
		$person = $this->_getPersonOr404($person_id, 'edit');

		foreach ($person->usergroups AS $key => $group) {
			if ($group->id == $usergroup_id) {
				if ($group->is_agent_group) {
					return $this->createApiErrorResponse('invalid_group', 'Group is an agent group');
				}
				unset($person->usergroups[$key]);
				$this->em->persist($person);
				$this->em->flush();
				break;
			}
		}

		return $this->createSuccessResponse();
	}

	public function getPersonLabelsAction($person_id)
	{
		$person = $this->_getPersonOr404($person_id);

		return $this->createApiResponse(array('labels' => $this->getApiData($person->labels)));
	}

	public function postPersonLabelsAction($person_id)
	{
		$person = $this->_getPersonOr404($person_id, 'edit');
		$label = $this->in->getString('label');

		if ($label === '') {
			return $this->createApiErrorResponse('required_field', "Field 'label' missing or empty");
		}

		$person->getLabelManager()->addLabel($label);
		$this->em->persist($person);
		$this->em->flush();

		return $this->createApiCreateResponse(
			array('label' => $label),
			$this->generateUrl('api_people_person_label', array('person_id' => $person->id, 'label' => $label), true)
		);
	}

	public function getPersonLabelAction($person_id, $label)
	{
		$person = $this->_getPersonOr404($person_id);

		if ($person->getLabelManager()->hasLabel($label)) {
			return $this->createApiResponse(array('exists' => true));
		} else {
			return $this->createApiResponse(array('exists' => false));
		}
	}

	public function deletePersonLabelAction($person_id, $label)
	{
		$person = $this->_getPersonOr404($person_id, 'edit');

		$person->getLabelManager()->removeLabel($label);
		$this->em->persist($person);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	public function getFieldsAction()
	{
		$field_manager = $this->container->getSystemService('person_fields_manager');
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

	public function isPersonEditable(Person $person)
	{
		if ($this->person->can_admin) {
			return true;
		}

		if ($person->is_agent && $person->getId() != $this->person->getId()) {
			return false;
		}

		return true;
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\Person
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function _getPersonOr404($id, $check_perm = '')
	{
		$person = $this->em->getRepository('DeskPRO:Person')->findOneById($id);

		if (!$person) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no person with ID $id");
		}

		if ($check_perm) {
			switch ($check_perm) {
				case 'edit':
				case 'delete':
				case 'reset_password':
				case 'manage_emails':
				case 'notes':
					if (!$this->person->hasPerm('agent_people.' . $check_perm) || !$this->isPersonEditable($person)) {
						throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
					}
					break;

				default:
					throw new \Exception("Uknown perm type $check_perm");
			}
		}

		return $person;
	}
}
