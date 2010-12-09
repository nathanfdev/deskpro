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
 * A radio field
 */
class Radio extends BooleanField
{
	public function getDefaultAttributes()
	{
		$attr = parent::getDefaultAttributes();

		// Radio fields are useless on their own, they have to be part of a group.
		// So the 'name' attribute of a set of radio buttons must all be the same
		// for them to work properly.
		if ($this->parent) {
			$parent_attr = $this->parent->getDefaultAttributes();
			$attr['name'] = $parent_attr['name'];
		}

		return $attr;
	}
}