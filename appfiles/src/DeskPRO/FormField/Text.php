<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Form
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\FormField\Type;

use \Application\CoreBundle\Entity;

/**
 * Text field
 */
class Text extends AbstractType
{
	/**
	 * Get the Orb\Form\Field object for this field type.
	 *
	 * @return Orb\Form\Field\Text
	 */
	public function getFormField(Entity\FormFieldData $form_field_data = null)
	{
		$options = array();
		if ($this->fielddef['options']['min_length']) {
			$options['min_length'] = $this->fielddef['options']['min_length'];
		}
		if ($this->fielddef['options']['max_length']) {
			$options['max_length'] = $this->fielddef['options']['max_length'];
		}

		$options['name'] = $this->getFormFieldName();

		$field = new \Orb\Form\Field\Text($options);

		if ($form_field_data) {
			$field->setData($form_field_data['data']['value']);
		}

		return $field;
	}


	/**
	 * Save the value from a form field into storage
	 *
	 * @param Orb\Form\Field\Field $formfield
	 * @param Entity\FormFieldData $form_field_data
	 */
	public function saveFormValue(\Orb\Form\Field\Field $formfield, Entity\FormFieldData $form_field_data)
	{
		$form_field_data['data'] = array('value' => $formfield->getData());
	}



	/**
	 * Render the field to HTML for use in a web page.
	 */
	public function renderHtml(Entity\FormFieldData $form_field_data)
	{
		return htmlspecialchars($this->renderText($form_field_data));
	}



	/**
	 * Render the field
	 */
	public function renderText(Entity\FormFieldData $form_field_data)
	{
		$value = '';
		if (isset($form_field_data['data']['value'])) {
			$value = $form_field_data['data']['value'];
		}

		return $value;
	}
}