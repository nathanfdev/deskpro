<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use \Orb\Util\Arrays;

use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\PersonEmail;
use \Application\DeskPRO\Entity\PersonContactData;
use \Application\DeskPRO\Entity\PersonNote;
use \Application\DeskPRO\Entity\Organization;

use \Application\DeskPRO\App;

/**
 * Handles viewing and editing a person
 */
class PersonController extends AbstractController
{
	############################################################################
	# /agent/people/:person_id                                   agent_people_view
	############################################################################

	public function viewAction($person_id)
	{
		if ($person_id) {
			$person = $this->getPersonOr404($person_id);
		} else {
			$person = new Person();
		}

		if (!$person['first_name'] && !$person['last_name'] && $person['name']) {
			$parts = explode(' ', $person['name'], 2);
			$parts = Arrays::removeFalsey($parts);

			if ($parts) {
				$person['first_name'] = $parts[0];
				if (isset($parts[1])) {
					$person['last_name'] = $parts[1];
				}
			}
		}

		$form = $this->_getForm($person);

		// Custom fields
		$field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		$data_structured = App::getApi('custom_fields.util')->createDataHierarchy($person['custom_data'], $field_defs);

		$custom_fields_form = new \Symfony\Component\Form\FieldGroup('custom_fields');
		$custom_fields = App::getApi('custom_fields.people')->getFieldsDisplayArray($field_defs, $data_structured, $custom_fields_form);

		#------------------------------
		# Contact fields: empty tpls
		#------------------------------

		$contact_fields_tpl = array();
		$f = new \Application\DeskPRO\Form\ContactFieldHandler\InstantMessage();
		$contact_fields_tpl['instant_message'] = $f->getFormField();

		$f = new \Application\DeskPRO\Form\ContactFieldHandler\Address();
		$contact_fields_tpl['address'] = $f->getFormField();

		$f = new \Application\DeskPRO\Form\ContactFieldHandler\Phone();
		$contact_fields_tpl['phone'] = $f->getFormField();

		#------------------------------
		# Latest 5 notes
		#------------------------------

		$em = App::getOrm();

		$notes = $em->createQuery("
			SELECT n
			FROM DeskPRO:PersonNote n
			WHERE n.person_id = ?1
			ORDER BY n.id DESC
		")->setParameter(1, $person['id'])->setMaxResults(5)->execute();

		$db = App::getDb();
		$notes_count = $db->fetchColumn("
			SELECT COUNT(*) FROM people_notes
			WHERE person_id = ?
		", array($person['id']));

		$note_pages = false;
		if ($notes_count > 5) {
			$note_pages = range(1, ceil($notes_count / 5));
		}


		// Used in the org dlg popup. TODO need to clean this up.
		// Likely be an autocomplete field in the dlg
		$org_options = $db->fetchAllKeyValue("
			SELECT id, name
			FROM organizations
			ORDER BY name ASC
		");
		$org_options = Arrays::implodeTemplate($org_options, "<option value=\"{KEY}\">{VAL}</option>");

		return $this->render('AgentBundle:Person:view.html.twig', array(
			'person' => $person,
			'form' => $form,
			'fields' => $form->getCustomFields(),
			'custom_fields' => $custom_fields,
			'contact_fields_tpl' => $contact_fields_tpl,
			'notes' => $notes,
			'note_pages' => $note_pages,
			'org_options' => $org_options
		));
	}

	############################################################################
	# /agent/people/:person_id/ajax-get-notes           agent_people_ajaxget_notes
	############################################################################

	public function ajaxGetNotesAction($person_id)
	{
		if ($person_id) {
			$person = $this->getPersonOr404($person_id);
		} else {
			$person = new Person();
		}

		$per_page = min($this->in->getUint('pp'), 20);
		$page = $this->in->getUint('p');
		if (!$page) {
			$page = 1;
		}

		$start = ($page - 1) * $per_page;

		$em = App::getOrm();

		$notes = $em->createQuery("
			SELECT n, a
			FROM DeskPRO:PersonNote n
			LEFT JOIN n.agent a
			WHERE n.person_id = ?1
			ORDER BY n.id DESC
		")->setParameter(1, $person['id'])
			->setMaxResults($per_page)
			->setFirstResult($start)
			->execute();

		$html = array();

		foreach ($notes as $note) {
			$html[] = $this->renderView('AgentBundle:Person:note-li.html.twig', array('note' => $note));
		}

		$html = implode('', $html);

		return $this->createJsonResponse(array(
			'success' => true,
			'person_id' => $person['id'],
			'notes_html' => $html,
			'page' => $page
		));
	}



	############################################################################
	# /agent/people/:person_id/ajax-save                     agent_people_ajaxsave
	############################################################################

	public function ajaxSaveAction($person_id)
	{
		if ($person_id) {
			$person = $this->getPersonOr404($person_id);
		} else {
			$person = new Person();
		}

		$form = $this->_getForm($person);

		switch ($this->in->getString('action')) {
			case 'name':
				$form['basic_fields']['first_name']->setFormData($_POST['edit_person']['basic_fields']['first_name']);
				$form['basic_fields']['last_name']->setFormData($_POST['edit_person']['basic_fields']['last_name']);
				if ($form->isValid()) {
					$form->savePerson($person);
					return $this->createJsonResponse(array(
						'success' => true,
						'person_id' => $person['id'],
						'html' => htmlspecialchars($person['first_name'] . ' ' . $person['last_name'])
					));
				}
				break;
		}
	}


	############################################################################
	# /agent/people/:person_id/ajax-save-organization        agent_people_ajaxsave_organization
	############################################################################

	public function ajaxSaveOrganizationAction($person_id)
	{
		if ($person_id) {
			$person = $this->getPersonOr404($person_id);
		} else {
			$person = new Person();
		}

		$org_id = $this->in->getUint('organization_id');
		if (!$org_id) {
			$person['organization_id'] = 0;
			$person['organization'] = null;
			$person['organization_position'] = '';

			$em = App::getOrm();
			$em->persist($person);
			$em->flush();
			return $this->createJsonResponse(array(
				'success' => true,
				'person_id' => $person['id'],
				'organization_name' => '',
				'organization_position' => '',
			));
		}

		$org = Organization::getRepository()->find($org_id);

		$person['organization'] = $org;
		$person['organization_position'] = $this->in->getString('organization_position');

		$em = App::getOrm();
		$em->persist($person);
		$em->flush();

		return $this->createJsonResponse(array(
			'success' => true,
			'person_id' => $person['id'],
			'organization_name' => $org['name'],
			'organization_position' => $person['organization_position'],
		));
	}


	############################################################################
	# /agent/people/:person_id/ajax-save-emails       agent_people_ajaxsave_emails
	############################################################################

	// TODO error checking
	public function ajaxSaveEmailsAction($person_id)
	{
		if ($person_id) {
			$person = $this->getPersonOr404($person_id);
		} else {
			$person = new Person();
		}

		$del_ids = $this->in->getCleanValueArray('del_ids', 'uint', 'discard');
		$new_emails = $this->in->getCleanValueArray('new_emails', 'string', 'discard');
		$primary_id = $this->in->getString('primary_id');

		$this->em->beginTransaction();

		if (ctype_digit($primary_id) AND $person['primary_email_id'] != $primary_id) {
			$email = $person->getEmailId($primary_id);
			$person['primary_email'] = $email;
			$person['primary_email_id'] = $primary_id;
		}

		foreach ($del_ids as $id) {
			$person->removeEmailAddressId($id);
		}

		foreach ($new_emails as $email_address) {
			$email = new PersonEmail();
			$email['email'] = $email_address;
			$email['is_validated'] = true;

			$person->addEmailAddress($email);

			if ($primary_id == $email['email']) {
				$person['primary_email'] = $email;
				$primary_id = $email['id'];
			}
		}

		$first_email = null;
		$found = false;
		foreach ($person['emails'] as $email) {
			if (!$first_email) $first_email = $email;
			if ($email['id'] == $primary_id) {
				$found = true;
				break;
			}
		}

		if (!$found) {
			$person['primary_email'] = $first_email;
		}

		$this->em->persist($person);
		$this->em->flush();
		$this->em->commit();

		return $this->createJsonResponse(array(
			'success' => true,
			'person_id' => $person['id'],
			'dlg_html' => $this->renderView('AgentBundle:Person:email-dlg-li.html.twig', array('person' => $person))
		));
	}


	############################################################################
	# /agent/people/:person_id/ajax-save-contact     agent_people_ajaxsave_contact
	############################################################################

	// TODO error checking
	public function ajaxSaveContactAction($person_id)
	{
		if ($person_id) {
			$person = $this->getPersonOr404($person_id);
		} else {
			$person = new Person();
		}

		$type = $this->in->getString('contact_type');
		$handler = \Application\DeskPRO\Form\ContactFieldHandler\AbstractContactFieldHandler::simpleNameToClassName($type);

		$handler = new $handler();

		$contact_data = new PersonContactData();
		$contact_data['handler_class'] = get_class($handler);

		foreach ($_POST[$handler->getSimpleName()] as $k => $v) {
			$field_k = $k;
			if ($k != 'comment') {
				$field_k = $handler->mapNameToField($k);
			}
			if (!$field_k) continue;

			$contact_data[$field_k] = $v;
		}

		$em = App::getOrm();
		$em->beginTransaction();
		$this->person->addContactData($contact_data);
		$em->persist($contact_data);
		$em->flush();
		$em->commit();

		return $this->createJsonResponse(array(
			'success' => true,
			'person_id' => $person['id'],
			'contact_html' => $this->renderView('AgentBundle:Person:contact-section.html.twig', array('person' => $person))
		));
	}

	############################################################################
	# /agent/people/:person_id/ajax-save-note           agent_people_ajaxsave_note
	############################################################################

	// TODO error checking
	public function ajaxSaveNoteAction($person_id)
	{
		if ($person_id) {
			$person = $this->getPersonOr404($person_id);
		} else {
			$person = new Person();
		}

		$note_txt = $this->in->getString('note');

		$em = App::getOrm();
		$em->beginTransaction();

		$note = new PersonNote();
		$note['agent'] = $this->person;
		$note['person'] = $person;
		$note['note'] = $note_txt;
		$em->persist($note);

		$em->flush();
		$em->commit();

		return $this->createJsonResponse(array(
			'success' => true,
			'person_id' => $person['id'],
			'note_li_html' => $this->renderView('AgentBundle:Person:note-li.html.twig', array('note' => $note))
		));
	}

	############################################################################
	# new
	############################################################################

	public function newAction()
	{
		$person = new Entity\Person();
		$person['first_name'] = $this->in->getString('person.first_name');
		$person['last_name'] = $this->in->getString('person.last_last');

		$email = new Entity\PersonEmail();
		$email['email'] = $this->in->getString('person_email.email');
		$person->addEmailAddress($email);

		App::getOrm()->persist($person);
		App::getOrm()->flush();

		$data = array(
			'id' => $person['id'],
			'name' => $person['display_name'],
			'email' => $person['primary_email_address'],
			'label' => $person['display_name'] . ($person['primary_email_address'] ? " <{$person['primary_email_address']}>" : '')
		);

		return $this->createJsonResponse($data);
	}

	############################################################################
	# ajax-save-custom-fields
	############################################################################

	public function ajaxSaveCustomFieldsAction($person_id)
	{
		$person = $this->getPersonOr404($person_id);

		$field_defs = App::getApi('custom_fields.people')->getEnabledFields();
		foreach ($field_defs as $field_def) {
			foreach ($field_def->getHandler()->getDataFromForm($_POST['custom_fields']) as $info) {
				$person->setCustomData($info[0], $info[1], $info[2]);
			}
		}

		App::getOrm()->persist($person);
		App::getOrm()->flush();

		$data_structured = App::getApi('custom_fields.util')->createDataHierarchy($person['custom_data'], $field_defs);
		$custom_fields = array();
		foreach ($field_defs as $f_def) {
			$f = $f_def->getHandler()->getFormField();

			$custom_fields[] = array(
				'field_def' => $f_def,
				'title' => $f_def['title'],
				'rendered' => $data_structured[$f_def['id']] ? $f_def->getHandler()->renderHtml($data_structured[$f_def['id']]) : false
			);
		}

		return $this->render('AgentBundle:Person:custom-fields-rendered.html.twig', array(
			'custom_fields' => $custom_fields,
		));
	}

	############################################################################
	# ajax-save-labels
	############################################################################

	public function ajaxSaveLabelsAction($person_id)
	{
		$person = $this->getPersonOr404($person_id);

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$person->getLabelManager()->setLabelsArray($labels);

		App::getOrm()->persist($person);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}


	############################################################################

	protected function _getForm(Person $person)
	{
		$form = new \Application\AgentBundle\Form\EditPerson(array('name' => 'edit_person'));

		// Custom fields
		//$fields = $this->em->getRepository('DeskPRO:PersonField')->getEnabledFields();
		//$form->setCustomFields($fields);

		$form->setPerson($person);

		$renderer = new \Orb\Form\Renderer\Basic();
		$form->setRenderer($renderer);

		return $form;
	}


	/**
	 * @return Application\DeskPRO\Entity\Person
	 */
	protected function getPersonOr404($person_id)
	{
		try {
			$person = $this->em->find('DeskPRO:Person', $person_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no person with ID $person_id");
		}

		return $person;
	}
}