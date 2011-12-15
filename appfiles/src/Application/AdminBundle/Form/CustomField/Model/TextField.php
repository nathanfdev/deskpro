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

namespace Application\AdminBundle\Form\CustomField\Model;

class TextField extends CustomFieldAbstract
{
	public $min_length;
	public $max_length;
	public $regex;

	public $agent_min_length;
	public $agent_max_length;
	public $agent_regex;

	public function init()
	{
		if ($this->_field->getOption('min_length')) {
			$this->validation_type = 'required';
			$this->min_length = $this->_field->getOption('min_length');
		}
		if ($this->_field->getOption('max_length')) {
			$this->validation_type = 'required';
			$this->max_length = $this->_field->getOption('max_length');
		}
		if ($this->_field->getOption('regex')) {
			$this->validation_type = 'regex';
			$this->regex = $this->_field->getOption('regex');
		}

		if ($this->_field->getOption('agent_min_length')) {
			$this->agent_validation_type = 'required';
			$this->agent_min_length = $this->_field->getOption('agent_min_length');
		}
		if ($this->_field->getOption('agent_max_length')) {
			$this->agent_validation_type = 'required';
			$this->agent_max_length = $this->_field->getOption('agent_max_length');
		}
		if ($this->_field->getOption('agent_regex')) {
			$this->agent_validation_type = 'regex';
			$this->agent_regex = $this->_field->getOption('agent_regex');
		}
	}

	protected function setFieldProperties()
	{
		$field = $this->_field;

		if ($this->validation_type == 'required') {
			$field->setOption('required', true);
			$field->setOption('min_length', $this->min_length);
			$field->setOption('max_length', $this->max_length);
		} elseif ($this->validation_type == 'regex') {
			$field->setOption('required', false);
			$field->setOption('regex', $this->regex);
		} else {
			$field->setOption('required', null);
			$field->setOption('regex', null);
			$field->setOption('min_length', null);
			$field->setOption('max_length', null);
		}

		if ($this->agent_validation_type == 'required') {
			$field->setOption('agent_required', true);
			$field->setOption('agent_min_length', $this->agent_min_length);
			$field->setOption('agent_max_length', $this->agent_max_length);
		} elseif ($this->agent_validation_type == 'regex') {
			$field->setOption('agent_required', false);
			$field->setOption('agent_regex', $this->agent_regex);
		} else {
			$field->setOption('agent_required', null);
			$field->setOption('agent_regex', null);
			$field->setOption('agent_min_length', null);
			$field->setOption('agent_max_length', null);
		}
	}
}
