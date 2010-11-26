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
			SELECT f.id, f.title, f.handler_class
			FROM person_fields f
			WHERE f.parent_id IS NULL
			ORDER BY f.id
		");

		return $this->render('TechBundle:PersonFields:index', array(
			'fields' => $existing_fields
		));
	}



	############################################################################
	# /tech/person-fields/new-choose-type      admin_personfields_new_choosetype
	############################################################################

	public function newChooseTypeAction()
	{
		return $this->render('TechBundle:PersonFields:edit-choosetype', array(
			
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
			$field['handler_class'] = $this->in->getString('formfield.handler_class');
		}

		// Cant edit a specific child field; the main parent field editor must be used
		if ($field['parent']) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("$field_id is not a valid field (it has a parent)");
		}

		$renderer = new \Orb\Form\Renderer\Basic();
		$form = new \Application\TechBundle\Form\EditField(array(
			'name' => 'formfield',
			'renderer' => $renderer,
			'event_dispatcher' => $this->get('event_dispatcher'),
			'form_field' => $field
		));
		$form->addField(new \Orb\Form\Field\Hidden(array('name' => 'handler_class', 'data' => $field['handler_class'])));

		$admin_handler = \Application\TechBundle\CustomField\AdminHandler\Factory::createFromFormField($field);
		$form->addField($admin_handler->buildFormGroup());

		if ($this->isPostRequest()) {
			$form->setFormData($_POST);
			if ($form->isValid()) {
				$admin_handler->saveField($form);
				$this->redirectRoute('admin_personfields_edit', array('field_id' => $field['id']));
			} else {
				// TODO proper handling
				print_r($form->getErrors());
			}
		}

		$parts = explode('\\', $field['handler_class']);
		$tpl_name = 'TechBundle:PersonFields:edit-' . strtolower(array_pop($parts));

		$vars = array_merge($admin_handler->getTemplateVars(), array(
			'field' => $field,
			'form' => $form,
		));

		return $this->render($tpl_name, $vars);
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