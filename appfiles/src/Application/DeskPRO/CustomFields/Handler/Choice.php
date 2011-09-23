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

use Application\DeskPRO\Entity;
use Application\DeskPRO\App;

/**
 * Handles the choice field
 */
class Choice extends HandlerAbstract
{
	public function renderHtml(array $data, array $template_vars = array())
	{
		$data['value'] = $this->_getRenderableString($data);
		return parent::renderText($data, $template_vars);
	}

	public function renderText(array $data, array $template_vars = array())
	{
		$data['value'] = $this->_getRenderableString($data);
		return  parent::renderText($data, $template_vars);
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

		$setData = null;
		if ($selected_options) {
			$setData = array_pop($selected_options);
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

		$value = null;
		if (!empty($form_data[$name])) {
			$value = $form_data[$name];
		}

		if ($value) {
			return array(
				array($value, 'value', 1)
			);
		}

		return array();
	}

	public function getSearchCapabilities()
	{
		return array('is', 'not');
	}

	public function getSearchType()
	{
		return 'id';
	}
}
