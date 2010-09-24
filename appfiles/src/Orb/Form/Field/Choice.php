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
 * A choice field can let the user select single or multiple.
 *
 * The default renderer also allows attribute options 'type' to be 'select', 'checkbox' (multiple), and 'radio' (single)
 */
class Choice extends Field
{
	const VAL_OPTGROUP = '__ORB_OPTGROUP__';
	
	const SELMODE_SINGLE   = 'single';
	const SELMODE_MULTIPLE = 'multiple';

	protected $choices = array();

	protected $selection_mode = 'single';

	public function init()
	{
		if ($this->hasOption('selection_mode')) {
			$this->selection_mode = $this->getOption('selection_mode');
		}
	}

	

	/**
	 * Set single or multiple selection mode. Use the SELMODE_* constants.
	 * 
	 * @param string $mode Mode to set
	 */
	public function setSelectionMode($mode)
	{
		$this->selection_mode = $mode;
	}


	
	/**
	 * Add a choice.
	 *
	 * Set a value of the constant VAL_OPTGROUP to start a new option group.
	 *
	 * @param string $value  The value of the option
	 * @param string $label  The label of the option
	 */
	public function addChoice($value, $label, $key = null)
	{
		$choice = array('value' => $value, 'label' => $label, 'type' => 'choice');

		if ($value == self::VAL_OPTGROUP) {
			$choice['type'] = 'sectionstart';
		}
		$this->choices[] = $choice;
	}



	/**
	 * Get an array of choices
	 *
	 * @param bool $flat A flat array of label=>value (also removes optgroups)?
	 * @return array
	 */
	public function getChoices($flat = false)
	{
		$arr = $this->choices;

		if ($flat) {
			$arr = array();
			foreach ($this->choices as $choice) {
				if ($choice['type'] != 'choice') continue;
				$arr[$choice['label']] = $choice['value'];
			}
		}

		return $arr;
	}

	

	/**
	 * Check to see if a certain value is selected
	 *
	 * @param string $value
	 * @return bool
	 */
	public function isValueSelected($value)
	{
		$val = $this->getFormData();
		if (!is_array($val)) {
			return $val == $value;
		} else {
			return in_array($value, $val);
		}
	}


	
	/**
	 * Get all the values of the choices.
	 *
	 * @return array
	 */
	public function getChoiceValues()
	{
		$values = $this->getChoices(true);
		$values = array_values($values);

		return $values;
	}


	public function getDefaultAttributes()
	{
		$attr = parent::getDefaultAttributes();

		unset($attr['value']);

		if ($this->selection_mode == self::SELMODE_MULTIPLE) {
			$attr['multiple'] = true;
		} else {
			unset($attr['multiple']);
		}

		return $attr;
	}
}