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
 * A text input field
 */
class BooleanCheckbox extends Field
{
	protected function init()
	{
		$this->addTransformer(new \Orb\Form\Transformer\StringBool());
	}

	public function getDefaultAttributes()
	{
		$attr = parent::getDefaultAttributes();
		$attr['type'] = 'checkbox';

		if ($this->hasOption('value')) {
			$attr['value'] = $this->getOption('value');
		} else {
			$attr['value'] = '0';
		}

		if ($attr['value'] !== '' AND $attr['value'] != '0') {
			$attr['checked'] = true;
		}

		return $attr;
	}
}