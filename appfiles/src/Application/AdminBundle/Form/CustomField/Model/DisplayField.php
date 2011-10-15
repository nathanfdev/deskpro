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

class DisplayField extends TextField
{
	public $html = '';

	public function init()
	{
		$this->html = $this->_field->getOption('html');
	}

	protected function setFieldProperties()
	{
		$field = $this->_field;

		$field->setOption('html', $this->html);
	}
}
