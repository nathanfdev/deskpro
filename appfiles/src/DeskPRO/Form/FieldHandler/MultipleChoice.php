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

namespace DeskPRO\Form\FieldHandler;

use \Application\CoreBundle\Entity;

/**
 * Multi-select field
 */
class MultipleChoice extends Choice
{
	/**
	 * @return Orb\Form\Field\Choice
	 */
	public function getFormField()
	{
		$options = array();
		$options['name'] = $this->getFormFieldName();
		$options['selection_mode'] = \Orb\Form\Field\Choice::SELMODE_MULTIPLE;

		$field = new \Orb\Form\Field\Choice($options);
		$field->addTransformer($this);

		foreach ($this->fielddef['field_children'] as $option_field) {
			$field->addChoice($option_field['id'], $option_field['title']);
		}

		return $field;
	}


	/**
	 * Render the field
	 */
	public function renderText(Entity\FormFieldData $form_field_data = null)
	{
		if (!$form_field_data) {
			return '';
		}

		$values = array();

		foreach ($form_field_data['data_children'] as $val) {
			$values[] = $val['title'];
		}

		return implode(', ', $values);
	}
}