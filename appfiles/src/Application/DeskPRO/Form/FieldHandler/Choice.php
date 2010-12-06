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

use \Application\CoreBundle\Entity;

use \Application\DeskPRO\App;

/**
 * Single-select field
 */
class Choice extends AbstractFieldHandler
{
	/**
	 * @return Orb\Form\Field\Choice
	 */
	public function getFormField()
	{
		$options = array();
		$options['name'] = $this->getFormFieldName();
		$options['selection_mode'] = \Orb\Form\Field\Choice::SELMODE_SINGLE;

		$field = new \Orb\Form\Field\Choice($options);
		$field->addTransformer($this);

		$field->addChoice(0, '');
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

		$value = '';

		// The first (and should be only) child has the value we want
		foreach ($form_field_data['data_children'] as $val) {
			$value = $val['title'];
			break;
		}

		return $value;
	}

	


	/**
	 * Transforms stored data to data the form controls can use
	 *
	 * @param  mixed $value     The stored data
	 * @return mixed
	 */
	public function transformStoredToForm($value)
	{
		return $value['selected_choices'];
	}



	/**
	 * Transforms form data into data we can store.
	 *
	 * If null, it signifies no value. Sometimes empty values can be significant,
	 * in which case the we'd still store an "empty" value in the database.
	 * But if you return null, no such record will be stored at all.
	 *
	 * @param  mixed $value     The form data
	 * @return mixed            The data we can store
	 */
	public function transformFormToStored($value)
	{
		if (!$value OR !is_array($value)) {
			return array('selected_choices' => array());
		}

		// Now check to make sure all choices are valid.
		// Any choice is an ID that refers to a 'child field' that
		// represents an option.

		$valid_ids = array();
		foreach ($this->fielddef['field_children'] as $option_field) {
			$valid_ids[] = $option_field['id'];
		}

		$selected_choices = array();
		foreach ($value as $id) {
			if (in_array($id, $valid_ids)) {
				$selected_choices[] = $id;
			}
		}

		return array('selected_choices' => $selected_choices);
	}



	/**
	 * Apply the transformed value to a field_data object.
	 */
	public function setValueOnData(Entity\FormFieldData $field_data, $value)
	{
		// value from transformFormToStored is just a simple array of ID's
		// But for proper storage we need to add 'child' values to field_data

		$field_data['data'] = $value;

		if ($value) {
			$em = App::getOrm();

			foreach ($this->fielddef['field_children'] as $option_field) {
				if (!in_array($option_field['id'], $value)) {
					continue;
				}

				$child_field_data = $field_data->createChildInstance();
				$field_data->addChildData($child_field_data);

				// Nothing else required, the mere existence of the record
				// declares it's "selected" state. There is no actual "value"
			}
		}
	}
}