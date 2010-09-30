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
use \Application\CoreBundle\Entity\FormField;
use \Application\CoreBundle\Entity\FormFieldAssociation;

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

		$form = new \Application\TechBundle\Form\Person();
		$fields = $this->em->getRepository('CoreBundle:FormFieldAssociation')->getFieldsForType(FormFieldAssociation::SYSTYPE_PERSON);
		$form->setCustomFields($fields);

		if ($this->isPostRequest()) {
			$form->setData($_POST);
			if ($form->isValid()) {
				$form->savePerson($person);
				echo "DONE";
			} else {
				// TODO proper handling
				print_r($form->getErrors());
			}
		}

		$this->render('TechBundle:People:view', array(
			'person' => $person,
			'form' => $form,
		));
	}



	############################################################################

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