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
 * Handles viewing and editing people
 */
class PeopleController extends AbstractController
{
	############################################################################
	# /tech/people/:person_id                                   tech_people_view
	############################################################################

	public function viewAction($person_id)
	{
		$person = $this->getPersonOr404($person_id);

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

		return $this->render('TechBundle:People:view', array(
			'person' => $person,
			'form' => $form,
		));
	}

	
	
	############################################################################
	# /tech/people/:person_id/ajax-save                     tech_people_ajaxsave
	############################################################################

	public function ajaxSaveAction($person_id)
	{
		return $this->createJsonResponse(array('yay' => '123'));

		$person = $this->getPersonOr404($person_id);

		$form->setData($_POST);
		if ($form->isValid()) {
			$form->savePerson();
		}
	}



	############################################################################
	
	protected function _getForm(Person $person)
	{
		$form = new \Application\TechBundle\Form\EditPerson(array('name' => 'edit_person'));
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