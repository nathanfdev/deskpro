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

class ChoiceField extends CustomFieldAbstract
{
	public $multiple = false;
	public $expanded = false;

	public $choices = array();

	protected function init()
	{
		if ($this->_field->getOption('multiple')) {
			$this->multiple = true;
		}
		if ($this->_field->getOption('expanded')) {
			$this->expanded = true;
		}
	}

	protected function setFieldProperties()
	{
		$field = $this->_field;

		$field->setOption('multiple', $this->multiple);
		$field->setOption('expanded', $this->expanded);
	}
}
