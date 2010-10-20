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

use \Application\CoreBundle\Entity\PersonField;

/**
 * Handles manaing person fields
 */
class PersonFieldsController extends AbstractController
{
	############################################################################
	# /tech/person-fields                                     admin_personfields
	############################################################################

	public function indexAction()
	{
		$existing_fields = $this->db->fetchAll("
			SELECT f.id, f.title, f.class_name
			FROM person_fields f
			ORDER BY f.id
		");

		return $this->render('TechBundle:Fields:index', array(
			'existing_fields' => $existing_fields
		));
	}


	
	############################################################################
	# /tech/person-fields/:field_id/edit                 admin_personfields_edit
	############################################################################

	public function editAction($field_id)
	{
		if ($field_id) {
			$field = $this->getFieldOr404($field_id);
		} else {
			$field = new PersonField();
			$field['handler_class'] = $this->in->getString('handler_class');
		}

		$renderer = new \Orb\Form\Renderer\Basic();
		$form = new \Application\TechBundle\Form\EditField(array(
			'name' => 'formfield',
			'renderer' => $renderer,
			'event_dispatcher' => $this['event_dispatcher'],
			'form_field' => $field
		));

		$admin_handler = \Application\TechBundle\FormField\AdminHandler\Factory::createFromFormField($field);
		$form->addField($admin_handler->buildFormGroup());

		if ($this->isPostRequest()) {
			$form->setData($_POST);
			if ($form->isValid()) {
				$admin_handler->saveField($form);
				echo "DONE";
			} else {
				// TODO proper handling
				print_r($form->getErrors());
			}
		}

		return $this->render('TechBundle:PersonFields:edit', array(
			'field' => $field,
			'form' => $form
		));
	}



	############################################################################
	# /tech/person-fields/:field_id/test                 admin_personfields_test
	############################################################################

	public function testAction()
	{
		// TODO show an example of what the field looks like rendered
	}



	############################################################################

	/**
	 * @return Application\CoreBundle\Entity\PersonField
	 */
	protected function getFieldOr404($field_id)
	{
		try {
			$field = $this->em->find('CoreBundle:PersonField', $field_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no field with ID $field_id");
		}

		return $field;
	}
}