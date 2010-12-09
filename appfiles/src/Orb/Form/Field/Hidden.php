<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Form\Field;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A hidden input field
 */
class Hidden extends Field
{
	public function getDefaultAttributes()
	{
		$attr = parent::getDefaultAttributes();
		$attr['type'] = 'hidden';
		return $attr;
	}
	
	public function __toString()
	{
		return $this->getData();
	}
}