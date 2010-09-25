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
 * A checkbox field
 *
 * This is just a boolean field. The separate classname is just to distinguish it.
 * Renderers are responsible for rendering the actual checkbox.
 */
class Checkbox extends BooleanField
{

}