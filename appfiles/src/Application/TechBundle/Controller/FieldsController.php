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
		$existing_fields = $this->db->fetchAllGrouped("
			SELECT
				f.id, f.title, f.typename,
				fa.sysname AS assoc_sysname
			FROM form_fields f
			INNER JOIN form_field_associations AS fa ON (fa.form_field_id = f.id)
			ORDER BY f.id
		", 'assoc_sysname', 'id');

		return $this->render('TechBundle:Fields:index', array('existing_fields' => $existing_fields));
	}


	
	############################################################################
	# /tech/fields/:field_id/edit                    tech_admin_fields_editfield
	############################################################################

	public function editFieldAction($field_id)
	{
		if ($field_id) {
			$field = $this->getFieldOr404($field_id);
		} else {
			$field = $this->em->createEntity('CoreBundle:FormField');
			$field['typename'] = $this->in->getString('typename');
		}

		$renderer = new \Orb\Form\Renderer\Basic();
		$form = new \Application\TechBundle\Form\EditField(array(
			'name' => 'formfield',
			'renderer' => $renderer,
			'event_dispatcher' => $this['event_dispatcher'],
			'form_field' => $field
		));

		if ($this->isPostRequest()) {
			$form->setData($_POST);
			if ($form->isValid()) {
				$this->_saveEditFieldForm($form, $field);
				echo "DONE";
			} else {
				// TODO proper handling
				print_r($form->getErrors());
			}
		}

		return $this->render('TechBundle:Fields:edit', array(
			'form' => $form
		));
	}

	protected function _saveEditFieldForm(\Application\TechBundle\Form\EditField $form, \Application\CoreBundle\Entity\FormField $formfield)
	{
		$this->em->beginTransaction();

		$is_new = ((bool)$formfield['id']);

		#------------------------------
		# Set form properties
		#------------------------------

		switch ($formfield['typename']) {
			case 'text':               $formfield['field_classname'] = 'Orb\\Form\\Field\\Text';
			case 'textarea':           $formfield['field_classname'] = 'Orb\\Form\\Field\\Textarea';
		}

		$formfield['title'] = $form['field_properties']['title']->getData();
		$formfield['field_options'] = $form['field_options']->getData();

		$this->em->persist($formfield);

		#------------------------------
		# Save associations
		#------------------------------

		if (!$is_new) {
			// Delete existing ones first
			$this->db->delete('form_field_associations', array('form_field_id' => $formfield['id']));
		}

		// Create them
		foreach ($formfield['field_associations'] as $sysname) {
			$formfield_assoc = $this->em->createEntity('Core:FormFieldAssociation');
			$formfield_assoc['form_field'] = $formfield;
			$formfield_assoc['sysname'] = $sysname;

			$this->em->persist($formfield_assoc);
		}

		#------------------------------
		# Run post-updates
		#------------------------------

		// TODO
		// Some fields might need cleanup for existing data. For example,
		// if a select field deleted an option, we might have to delete the
		// fields that use that option.


		#------------------------------
		# Save
		#------------------------------

		$this->em->flush();
		$this->em->commit();
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