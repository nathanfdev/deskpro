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

/**
 * Handles creating/editing of fields
 */
class FieldsController extends AbstractController
{
	############################################################################
	# /tech/fields                                             tech_admin_fields
	############################################################################

	public function indexAction()
	{

	}


	
	############################################################################
	# /tech/fields/:field_id/edit                    tech_admin_fields_editfield
	############################################################################

	public function editFieldAction($field_id)
	{
		if ($field_id) {
			$field = $this->getFieldOr404($field_id);
		} else {
			$field = new$this->em->createEntity('CoreBundle:FormField');
			$field['typename'] = $this->in->getString('typename');
		}

		$renderer = new \Orb\Form\Renderer\Basic();
		$form = new \Orb\Form\Field\Form(array(
			'name' => 'field',
			'renderer' => $renderer,
			'event_dispatcher' => $this['event_dispatcher']
		));

		if (!$field['id']) {
			$f = new \Orb\Form\Field\Hidden(array('name' => 'typename'));
			$f->setData($field['typename']);
			$form->addField($f);
		}

		$form_field_options = new \Orb\Form\Field\FieldGroup(array('name' => 'field_options'));
		$f = new \Orb\Form\Field\Text(array('name' => 'label'));
		$f->setData($field['field_options']['label']);

		$form->addField($form_field_options);

		if ($this->isPostRequest()) {
			$form->setData($_POST);
			if ($form->isValid()) {
				$field['field_options'] = $form->getField('field_options')->getData();
				$field->save();
			} else {
				// TODO proper handling
				print_r($form->getErrors());
			}
		}

		return $this->render('TechBundle:Fields:edit', array(
			'form' => $form
		));
	}



	############################################################################
	# /tech/fields/:field_id/test                    tech_admin_fields_testfield
	############################################################################

	public function testFieldAction()
	{
		// TODO show an example of what the field looks like rendered
	}



	############################################################################

	/**
	 * @return Application\CoreBundle\Entity\FormField
	 */
	protected function getFieldOr404($field_id)
	{
		try {
			$field = $this->em->find('CoreBundle:FormField', $field_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no field with ID $field_id");
		}

		return $field;
	}
}