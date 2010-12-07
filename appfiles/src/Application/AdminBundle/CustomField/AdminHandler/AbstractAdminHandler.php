<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\AdminBundle\CustomField\AdminHandler;

use \Application\DeskPRO\Entity\CustomDefAbstract;

use \Application\DeskPRO\App;

/**
 * An admin handler that helps with building a custom field (options and the like).
 * Since each custom field is different and has its own options, each form for
 * creating or editing a field is different -- thats what these handlers do.
 *
 * // TODO: A lot of this saving stuff needs to be moved a DeskPRO\ helper class
 * and extended to the API.
 *
 * @see Application\AdminBundle\Controller\FieldsController
 */
abstract class AbstractAdminHandler
{
	/**
	 * The form field definition
	 * @var Application\DeskPRO\Entity\CustomDefAbstract
	 */
	protected $custom_def;

	protected $em;

	public function __construct(CustomDefAbstract $custom_def)
	{
		$this->custom_def = $custom_def;
		$this->em = App::getOrm();

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
	 * Set data/options based on the current field defition.
	 * 
	 * @param \Orb\Form\Field\FieldGroup $formgroup
	 */
	public function setDataOnFormGroup(\Orb\Form\Field\FieldGroup $formgroup)
	{
		$formgroup->setData($this->custom_def['data']);
	}




	/**
	 * Return an array of fields we need to add to the form.
	 *
	 * @return array
	 */
	abstract protected function buildRequiredFormFields();


	
	/**
	 * Gets variables we'll need to use in the template.
	 *
	 * @return array
	 */
	public function getTemplateVars()
	{
		return array();
	}


	
	/**
	 * Save the field
	 * 
	 * @param \Orb\Form\Field\Form $form
	 */
	public function saveField(\Orb\Form\Field\Form $form)
	{
		$this->em->beginTransaction();

		$is_new = ((bool)$this->custom_def['id']);

		$this->custom_def['title'] = $form['field_properties']['title']->getData();
		$this->em->persist($this->custom_def); // need to save now 'cuz might add children, which will need the parent

		$this->handleSave($form['fieldtype_form']);

		$this->em->persist($this->custom_def); // save again incase changes made from handler

		#------------------------------
		# Save
		#------------------------------

		$this->em->flush();
		$this->em->commit();
	}


	
	/**
	 * Save options for the current field.
	 *
	 * @param Orb\Form\Field\FieldGroup $formgroup This is the form fragment for this type
	 */
	abstract protected function handleSave(\Orb\Form\Field\FieldGroup $formgroup);
}