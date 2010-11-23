<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage TechBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\TechBundle\Controller;

use \Orb\Util\Arrays;

use \Application\CoreBundle\Entity\Person;
use \Application\CoreBundle\Entity\PersonEmail;

/**
 * Handles viewing and editing a person
 */
class PersonController extends AbstractController
{
	############################################################################
	# /tech/people/:person_id                                   tech_people_view
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

		if ($this->isPostRequest()) {
			$form->setFormData($_POST);
			if ($form->isValid()) {

				$this->em->beginTransaction();

				$form->savePerson($person);

				$this->em->commit();
			} else {
				// TODO proper handling
				print_r($form->getErrors());
			}
		}

		// All-in-one array for template
		$custom_fields = array();
//		foreach ($form->getCustomFields() as $f) {
//			$form_field = $form['custom_fields']['field_' . $f['id']];
//			$custom_fields[] = array(
//				'id' => $f['id'],
//				'html_id' => $form_field->getFormId(),
//				'rendered_value' => $person->renderSingleFieldValue($f['id']),
//				'field_def' => $f,
//				'form_field' => $form_field
//			);
//		}

		return $this->render('TechBundle:Person:view', array(
			'person' => $person,
			'form' => $form,
			'fields' => $form->getCustomFields(),
			'custom_fields' => $custom_fields,
		));
	}



	############################################################################
	# /tech/people/:person_id/ajax-save                     tech_people_ajaxsave
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

		if (!$form->isValid()) {

		}

		/*
		$form->setFormData($_POST);

		if ($form->isValid()) {
			$form->savePerson($person);
			return $this->createJsonResponse(array('success' => true, 'person_id' => $person['id']));
		} else {
			return $this->createJsonResponse(array('error' => true));
		}*/
	}


	############################################################################
	# /tech/people/:person_id/ajax-save-emails       tech_people_ajaxsave_emails
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
			'dlg_html' => $this->renderView('TechBundle:Person:email-dlg-li', array('person' => $person))
		));
	}



	############################################################################

	protected function _getForm(Person $person)
	{
		$form = new \Application\TechBundle\Form\EditPerson(array('name' => 'edit_person'));

		// Custom fields
		//$fields = $this->em->getRepository('CoreBundle:PersonField')->getEnabledFields();
		//$form->setCustomFields($fields);

		$form->setPerson($person);

		$renderer = new \Orb\Form\Renderer\Basic();
		$form->setRenderer($renderer);

		return $form;
	}


	/**
	 * @return Application\CoreBundle\Entity\Person
	 */
	protected function getPersonOr404($person_id)
	{
		try {
			$person = $this->em->find('CoreBundle:Person', $person_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no person with ID $person_id");
		}

		return $person;
	}
}