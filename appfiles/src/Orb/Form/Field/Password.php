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
 */
class Password extends Text
{
	public function getDefaultAttributes()
	{
		$attr = parent::getDefaultAttributes();
		$attr['type'] = 'password';

		if ($this->getOption('always_empty')) {
			$attr['value'] = '';
		}

		return $attr;
	}
}