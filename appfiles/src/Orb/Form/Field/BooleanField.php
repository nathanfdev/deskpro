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
 * A field that can be on or off (checkboxes, radio)
 *
 * @option  string  value          The value of the field. Defaults to '1'
 * @option  bool    checked        Override to force the field checked or not
 * @option  bool    no_force_bool  Do not add the StringBool transformer. This means when a
 *                                 user checks the choice, you get a boolean 1 value instead of
 *                                 the real value specified in the 'value' option.
 *                                 In most cases you only care about on/off, so the actual
 *                                 value you set doesn't matter. This is false by default.
 */
abstract class BooleanField extends Field
{
	protected function init()
	{
		if ($this->getOption('no_force_bool')) {
			$this->addTransformer(new \Orb\Form\Transformer\StringBool());
		}
	}

	public function getDefaultAttributes()
	{
		$attr = parent::getDefaultAttributes();

		// Thing to remember is that 'value' here is not like other fields.
		// 'value' is the systems value that is defined.
		// A users input is always boolean, on or off.

		// So this value is always passed in specifically
		// Or we use a dummy value 1 to signify truthiness
		$attr['value'] = $this->getOption('value', 1);

		// Now the checked state is enabled when the user-defined 'form data' value
		// is not falsey. Sometimes it might be somethign like '1',
		// and other times it might be the string 'value' itself
		// if the StringBool transformer wasn't added.
		$attr['checked'] = $this->isChecked();

		return $attr;
	}


	
	/**
	 * Is this field checked?
	 *
	 * @return bool
	 */
	public function isChecked()
	{
		return (trim($this->getFormData()) !== '' AND $this->getFormData() !== 0 AND $this->getFormData() !== false);
	}

	

	public function __toString()
	{
		if ($this->isChecked()) {
			return '1';
		} else {
			return '0';
		}
	}
}