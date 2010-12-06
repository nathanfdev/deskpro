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

namespace Application\DeskPRO\Form\FieldHandler;

use \Application\DeskPRO\App;

use \Application\DeskPRO\Entity;

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


	/**
	 * Apply the transformed value to a field_data object.
	 *
	 * This should be called within a transaction, because child values may be added and persisted.
	 */
	public function setValueOnData(Entity\FormFieldData $field_data, $value)
	{
		$em = App::getOrm();
		
		// Save an array of selected values.
		// This is so we dont have to look up the entire collection
		// just to render it :-)
		$field_data['data'] = array('selected_choices' => $value);
		$em->persist($field_data);

		//
		// And now create the sub-data fields
		//

		$have = array();
		foreach ($field_data['data_children'] as $child) {
			if (!in_array($child['field_id'], $value)) {
				$field_data['data_children']->remove($child);
			} else {
				$have[] = $child['field_id'];
			}
		}

		foreach ($value as $id) {
			if (!in_array($id, $have)) {
				$child = $field_data->createChildInstance();
				$child['parent'] = $field_data;
				$child['person_field_id'] = $id;
				$field_data->addChildData($child);

				$em->persist($child);
			}
		}

		$em->persist($field_data);
	}
}