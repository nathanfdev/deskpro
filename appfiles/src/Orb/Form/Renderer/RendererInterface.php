<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Form\Renderer;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A renderer renders an HTML input field
 */
interface RendererInterface
{
	/**
	 * Render the field
	 *
	 * @param  Orb\Form\Field\Field $field       The field that needs rendering
	 * @param  array                $attributes  Attributes of the field
	 * @return string HTML
	 */
	public function renderField(\Orb\Form\Field\Field $field, array $attributes = array());
}