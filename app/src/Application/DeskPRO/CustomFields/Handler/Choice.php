<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Form
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

		if ($this->field_def->field_manager) {
			$children = $this->field_def->field_manager->getFieldChildren($this->field_def);
		} else {
			$children = $this->field_def['children'];
		}

		// Index array
		$children = \Orb\Util\Arrays::keyFromData($children, 'id');

		foreach ($children as $child) {
			$id = $child['id'];
			if (isset($data['children'][$id]) AND isset($data['children'][$id]['value'])) {
				$parent_title = '';
				if ($child->getOption('parent_id')) {
					$parent_title = $children[$child->getOption('parent_id')]->getTitle() . ' > ';
				}
				$val[] = $parent_title . $child['title'];
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

		if ($this->field_def->field_manager) {
			$children = $this->field_def->field_manager->getFieldChildren($this->field_def);
		} else {
			$children = $this->field_def['children'];
		}

		// Index array
		$children = \Orb\Util\Arrays::keyFromData($children, 'id');

		// Add options
		$has_children = array();
		foreach ($children as $child) {
			if ($child->getOption('parent_id')) {
				$has_children[$child->getOption('parent_id')] = true;
			}
		}

		foreach ($children as $child) {
			if (isset($has_children[$child->getId()])) {
				$options[$child->getTitle()] = array();
			} elseif ($child->getOption('parent_id')) {
				$title = $children[$child->getOption('parent_id')]->getTitle();
				$options[$title][$child->getId()] = $child->getTitle();
			} else {
				$options[$child->getId()] = $child->getTitle();
			}
		}

		foreach ($children as $child) {
			$id = $child['id'];
			if ($child['handler_class']) {
				$has_other = $id;
			} else {
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
