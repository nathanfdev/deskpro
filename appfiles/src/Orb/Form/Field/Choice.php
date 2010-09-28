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
 * A choice field is a select box, either single selection or multiple.
 * Choices are made up of Checkbox or Radio fields.
 */
class Choice extends FieldGroup
{
	const SELMODE_SINGLE = 'single';
	const SELMODE_MULTIPLE = 'multiple';

	public function init()
	{
		$this->addAllowedFieldType('Orb\\Form\\Field\\Checkbox');
		$this->addAllowedFieldType('Orb\\Form\\Field\\Radio');
	}


	public function isFieldAllowed(Field $field)
	{
		if (!parent::isFieldAllowed($field)) {
			return false;
		}

		// Make sure its correct based on field type.
		if ($this->getOption('selection_mode') == self::SELMODE_SINGLE) {
			if (!($field instanceof Orb\Form\Field\Radio)) {
				return false;
			}
		} else {
			if (!($field instanceof Orb\Form\Field\Checkbox)) {
				return false;
			}
		}

		return true;
	}

	public function getDefaultAttributes()
	{
		$attr = parent::getDefaultAttributes();

		unset($attr['value']);

		if ($this->getOption('selection_mode') == self::SELMODE_MULTIPLE) {
			$attr['name'] .= '[]';
			$attr['multiple'] = 'multiple';
			$attr['size'] = min(4, $this->count());
		}

		return $attr;
	}

	

	/**
	 * Quickly add a choice.
	 *
	 * @param string $value
	 * @param string $label
	 */
	public function addChoice($value, $label)
	{
		$name = $value;

		$opts = array(
			'name' => $name,
			'value' => $value,
			'label' => $label
		);

		if ($this->getOption('selection_mode') == self::SELMODE_SINGLE) {
			$field = new \Orb\Form\Field\Radio($opts);
		} else {
			$field = new \Orb\Form\Field\Checkbox($opts);
		}

		$this->addField($field);
	}



	protected function __toString()
	{
		$choices = array();

		foreach ($this as $choice) {
			if ($choice->isChecked()) {
				$choices[] = $choice->getOption('label');
			}
		}

		return implode(', ', $choices);
	}
}