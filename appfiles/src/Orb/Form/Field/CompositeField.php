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
 * A composite field is a special kind of field group that groups a number of fields
 * that all describe a single 'thing'.
 *
 * For example, a logical grouping of 'basic properties' on an object is just
 * a collection of independant fields. However, a 'date' field might have three
 * select boxes YYYY-MM-DD but they are all describing a single thing.
 *
 * The difference becomes quite important in certain circumstances.
 */
abstract class CompositeField extends FieldGroup
{
	public function isCompositeField()
	{
		return true;
	}
}