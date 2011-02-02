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

namespace Application\AdminBundle\CustomField\AdminHandler;

/**
 * Date type
 */
class Date extends AbstractAdminHandler
{
	/**
	 * Return an array of fields we need to add to the form.
	 *
	 * @return array
	 */
	protected function buildRequiredFormFields()
	{
		$fields = array();

		$f = new \Orb\Form\Field\Text(array(
			'name' => 'min_date',
			'attributes' => array('length' => 10)
		));
		$fields[] = $f;

		$f = new \Orb\Form\Field\Text(array(
			'name' => 'max_date',
			'attributes' => array('length' => 4)
		));
		$fields[] = $f;

		return $fields;
	}


	/**
	 * Save options for the current field.
	 *
	 * @param Orb\Form\Field\FieldGroup $form This is the form fragment for this type
	 */
	protected function handleSave(\Orb\Form\Field\FieldGroup $formgroup)
	{
		$this->custom_def['options'] = $formgroup->getData();
	}
}