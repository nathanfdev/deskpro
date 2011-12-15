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
	}

	protected function setFieldProperties()
	{
		$field = $this->_field;

		if ($this->validation_type == 'required') {
			$field->setOption('required', $this->required);
			$field->setOption('min_length', $this->min_length);
			$field->setOption('max_length', $this->max_length);
		} elseif ($this->validation_type == 'regex') {
			$field->setOption('required', false);
			$field->setOption('regex', $this->regex);
		}
	}
}
