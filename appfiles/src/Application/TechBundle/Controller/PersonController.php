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

use \Application\CoreBundle\Entity\Person;

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

		$form = $this->_getForm($person);

		if ($this->isPostRequest()) {
			$form->setData($_POST);
			if ($form->isValid()) {
				$form->savePerson($person);
			} else {
				// TODO proper handling
				print_r($form->getErrors());
			}
		}

		// All-in-one array for template
		$custom_fields = array();
		foreach ($form->getCustomFields() as $f) {
			$form_field = $form['custom_fields']['field_' . $f['id']];
			$custom_fields[] = array(
				'id' => $f['id'],
				'html_id' => $form_field->getFormId(),
				'rendered_value' => $person->renderSingleFieldValue($f['id']),
				'field_def' => $f,
				'form_field' => $form_field
			);
		}

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
		$form->setFormData($_POST);

		if ($form->isValid()) {
			$form->savePerson($person);
			return $this->createJsonResponse(array('success' => true, 'person_id' => $person['id']));
		} else {
			return $this->createJsonResponse(array('error' => true));
		}
	}



	############################################################################

	protected function _getForm(Person $person)
	{
		$form = new \Application\TechBundle\Form\EditPerson(array('name' => 'edit_person'));

		// Custom fields
		$fields = $this->em->getRepository('CoreBundle:PersonField')->getEnabledFields();

		$form->setCustomFields($fields);

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