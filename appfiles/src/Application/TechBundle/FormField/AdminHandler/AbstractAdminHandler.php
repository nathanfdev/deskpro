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

namespace Application\TechBundle\FormField\AdminHandler;

use \Application\CoreBundle\Entity\FormField;

/**
 * An admin handler that helps with building a custom field (options and the like).
 * Since each custom field is different and has its own options, each form for
 * creating or editing a field is different -- thats what these handlers do.
 *
 * @see Application\TechBundle\Controller\FieldsController
 */
abstract class AbstractAdminHandler
{
	/**
	 * The form field definition
	 * @var Application\CoreBundle\Entity\FormField
	 */
	protected $form_field;

	protected $em;

	public function __construct(FormField $form_field, $em)
	{
		$this->form_field = $form_field;
		$this->em = $em;

		$this->init();
	}


	
	/**
	 * Empty hook method
	 */
	protected function init() {}



	/**
	 * Get an array of additional fields to add to the Form object in the controller.
	 * This must always be called 'fieldtype_form'.
	 *
	 * @return \Orb\Form\Field\FieldGroup
	 */
	public function buildFormGroup()
	{
		$formgroup = new \Orb\Form\Field\FieldGroup(array('name' => 'fieldtype_form'));

		foreach ($this->buildRequiredFormFields() as $f) {
			$formgroup->addField($f);
		}

		return $formgroup;
	}


	
	/**
	 * Return an array of fields we need to add to the form.
	 *
	 * @return array
	 */
	abstract protected function buildRequiredFormFields();


	
	/**
	 * This renders the HTML for a particular field types options. Standard options
	 * always exist, like a title, but the rest is per-custom field type.
	 *
	 * @return string
	 */
	public function renderFormPartial($controller, $form)
	{
		$classname = get_class($this);
		$parts = explode('\\', $classname);
		$basename = array_pop($parts);

		$tplname = 'TechBundle:Fields:_edit_' . strtolower($basename);

		return $this->controller->renderView($tplname, array('form_field' => $this->form_field, 'form' => $form['fieldtype_form'], 'full_form' => $form));
	}


	
	/**
	 * Save the field
	 * 
	 * @param \Orb\Form\Field\Form $form
	 */
	public function saveField(\Orb\Form\Field\Form $form)
	{
		$this->em->beginTransaction();

		$is_new = ((bool)$this->formfield['id']);

		$this->formfield['title'] = $form['field_properties']['title']->getData();
		$this->em->persist($formfield);

		$this->handleSave($form['fieldtype_form']);

		#------------------------------
		# Save associations
		#------------------------------

		if (!$is_new) {
			// Delete existing ones first
			$this->db->delete('form_field_associations', array('form_field_id' => $formfield['id']));
		}

		// Create them
		foreach ($form['field_associations']->getData() as $sysname) {
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


	
	/**
	 * Save options for the current field.
	 *
	 * @param Orb\Form\Field\FieldGroup $form This is the form fragment for this type
	 */
	abstract protected function handleSave(\Orb\Form\Field\FieldGroup $form);
}