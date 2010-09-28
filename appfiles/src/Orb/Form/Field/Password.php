<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Form\Field;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A password input field
 *
 * @option  bool  always_empty   To always make this field empty
 */
class Password extends Text
{
	public function getDefaultAttributes()
	{
		$attr = parent::getDefaultAttributes();

		if ($this->getOption('always_empty')) {
			$attr['value'] = '';
		}

		return $attr;
	}

	public function __toString()
	{
		return ($this->getData() ? '********' : '');
	}
}