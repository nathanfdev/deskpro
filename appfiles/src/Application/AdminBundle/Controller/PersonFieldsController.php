<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;

use Application\DeskPRO\Entity\CustomDefPerson;

/**
 * Handles manaing person fields
 */
class PersonFieldsController extends AbstractController
{
	############################################################################
	# /agent/person-fields                                     admin_personfields
	############################################################################

	public function indexAction()
	{
		$existing_fields = App::getApi('custom_fields.people')->getFields();

		return $this->render('AdminBundle:PersonFields:index.twig', array(
			'fields' => $existing_fields
		));
	}



	############################################################################
	# /agent/person-fields/new-choose-type      admin_personfields_new_choosetype
	############################################################################

	public function newChooseTypeAction()
	{
		return $this->render('AdminBundle:PersonFields:edit-choosetype.twig', array(
			
		));
	}



	############################################################################
	# /agent/person-fields/:field_id/edit                 admin_personfields_edit
	############################################################################

	public function editAction($field_id)
	{
		if ($field_id) {
			$field = $this->getFieldOr404($field_id);
		} else {
			$field = new CustomDefPerson();
			$field['handler_class'] = $this->in->getString('formfield.handler_class');
		}

		// Cant edit a specific child field; the main parent field editor must be used
		if ($field['parent']) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("$field_id is not a valid field (it has a parent)");
		}

		$renderer = new \Orb\Form\Renderer\Basic();
		$form = new \Application\AdminBundle\Form\EditField(array(
			'name' => 'formfield',
			'renderer' => $renderer,
			'event_dispatcher' => $this->get('event_dispatcher'),
			'custom_def' => $field
		));
		$form->addField(new \Orb\Form\Field\Hidden(array('name' => 'handler_class', 'data' => $field['handler_class'])));

		$admin_handler = \Application\AdminBundle\CustomField\AdminHandler\Factory::createFromFormField($field);
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
		$tpl_name = 'AdminBundle:PersonFields:edit-' . strtolower(array_pop($parts)) . '.twig';

		$vars = array_merge($admin_handler->getTemplateVars(), array(
			'field' => $field,
			'form' => $form,
		));

		return $this->render($tpl_name, $vars);
	}



	############################################################################
	# /agent/person-fields/:field_id/test                 admin_personfields_test
	############################################################################

	public function testAction()
	{
		// TODO show an example of what the field looks like rendered
	}



	############################################################################

	/**
	 * @return Application\DeskPRO\Entity\CustomDefPerson
	 */
	protected function getFieldOr404($field_id)
	{
		try {
			$field = $this->em->find('DeskPRO:CustomDefPerson', $field_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no field with ID $field_id");
		}

		return $field;
	}
}