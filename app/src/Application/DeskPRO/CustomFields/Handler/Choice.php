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
	protected $multiple = false;
	protected $expanded = false;

	public function init()
	{
		$this->multiple = $this->field_def->getOption('multiple', false);
		$this->expanded = $this->field_def->getOption('expanded', false);
	}

	public function renderHtml(array $data = null, array $template_vars = array())
	{
		if ($data === null) return '';

		$data['value'] = $this->_getRenderableString($data);
		return parent::renderText($data, $template_vars);
	}

	public function renderText(array $data = null, array $template_vars = array())
	{
		if ($data === null) return '';

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

		$setData = $selected_options;
		if (!$this->multiple) {
			$setData = array_pop($setData);
		}

		$field_opts = array(
			'choices' => $options,
			'required' => false,
		);
		if ($this->multiple) {
			$field_opts['multiple'] = true;
		}
		if ($this->expanded) {
			$field_opts['expanded'] = true;
		}

		$field_choice = App::getFormFactory()->createNamedBuilder('choice', $this->getFormFieldName(), null, $field_opts);
		if ($setData) {
			$field_choice->setData($setData);
		}

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
			if (is_array($value)) {
				// Multiple selections in the form of field_1[] = childid
				$ret = array();
				foreach ($value as $k) {
					$ret[] = array($k, 'value', 1);
				}
			} else {
				// Single selections in the form of field_1 = childid
				$ret = array(
					array($value, 'value', 1)
				);
			}

			return $ret;
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
