<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Form
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\CustomFields\Handler;

use \Application\DeskPRO\Entity;
use Application\DeskPRO\App;

/**
 * Handles the choice field
 */
class Choice extends HandlerAbstract
{
	public function renderHtml(array $data, array $template_vars = array())
	{
		$data['value'] = $this->_getRenderableString($data);
		parent::renderText($data, $template_vars);
	}

	public function renderText(array $data, array $template_vars = array())
	{
		$data['value'] = $this->_getRenderableString($data);
		parent::renderText($data, $template_vars);
	}

	protected function _getRenderableString($data)
	{
		$val = array();

		foreach ($this->field_def['children'] as $child) {
			$id = $child['id'];
			if (isset($data['children'][$id]) AND isset($data['children'][$id]['value'])) {
				$val[] = $child['title'];
			}
		}

		$val = implode(', ', $val);

		return $val;
	}

	public function getFormField(array $data = null)
	{
		$options = array();
		$has_other = false;

		$selected_options = array();

		foreach ($this->field_def['children'] as $child) {
			$id = $child['id'];
			if ($child['handler_class']) {
				$has_other = $id;
			} else {
				$options[$id] = $child['title'];

				if (isset($data['children'][$id]) AND isset($data['children'][$id]['value'])) {
					$selected_options[] = $id;
				}
			}
		}

		/* Symfony bug to do with nested arrays? causes warning, for now lets just use a normal select
		$field_group = new \Symfony\Component\Form\Form($this->getFormFieldName(), array('required' => false));
		$field_choice = new \Symfony\Component\Form\ChoiceField('choice', array(
			'choices' => $options,
			'required' => false,
		));
		if ($selected_options) {
			$field_choice->setData($selected_options);
		}
		$field_group->add($field_choice);

		if ($has_other) {
			$field_other = new \Symfony\Component\Form\TextField('other');
			$field_group->add($field_other);
		}

		return $field_group;
		 */

		$setData = null;
		if ($selected_options) {
			$setData = $selected_options;
		}
		$field_choice = App::getFormFactory()->createNamedBuilder('choice', $this->getFormFieldName(), $setData, array(
			'choices' => $options,
			'required' => false,
		));

		return $field_choice;
	}

	function getDataFromForm(array $form_data)
	{
		$name = $this->getFormFieldName();

		if (!isset($form_data[$name])) $form_data[$name] = null;
		$form_data[$name] = array('choice' => $form_data[$name]);// TODO fix for above

		$from_data_choices = null;
		if (isset($form_data[$name]['choice'])) {
			$form_data[$name]['choice'] = (array)$form_data[$name]['choice'];
		}

		$all_values = array();

		foreach ($this->field_def['children'] as $child) {
			$value = array($child['id'], 'value', null);
			// "other" field
			if ($child['handler_class']) {
				$value[1] = 'input';

				if (isset($form_data[$name]['other'])) {
					$value[2] = $form_data[$child['id']];
				} else {
					$value[2] = null;
				}

			// Normal option
			} else {
				if (isset($form_data[$name]['choice']) AND in_array($child['id'], $form_data[$name]['choice'])) {
					$value[2] = 1;
				}
			}

			$all_values[] = $value;
		}

		return $all_values;
	}

	public function getSearchCapabilities()
	{
		return array('contains', 'notcontains');
	}

	public function getSearchType()
	{
		return 'id';
	}
}