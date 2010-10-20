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

use \DeskPRO\App;

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
	protected $fielddef;

	protected $em;

	public function __construct(FormField $fielddef)
	{
		$this->fielddef = $fielddef;
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
		$formgroup->setData($this->fielddef['data']);
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

		return $this->controller->renderView($tplname, array('fielddef' => $this->fielddef, 'form' => $form['fieldtype_form'], 'full_form' => $form));
	}


	
	/**
	 * Save the field
	 * 
	 * @param \Orb\Form\Field\Form $form
	 */
	public function saveField(\Orb\Form\Field\Form $form)
	{
		$this->em->beginTransaction();

		$is_new = ((bool)$this->fielddef['id']);

		$this->fielddef['title'] = $form['field_properties']['title']->getData();

		$this->handleSave($form['fieldtype_form']);

		$this->em->persist($this->fielddef);

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