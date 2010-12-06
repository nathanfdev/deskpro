<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Form
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\Form;

use \Orb\Util\Arrays;

/**
 * A helper that helps build and respond to the DeskPRO/InlineEdit.js system.
 */
class InlineEditHandler
{
	/**
	 * Input data
	 * @var array
	 */
	protected $input_data;

	/**
	 * A flat array of names we got from input.
	 * array(person => array(basic => array(fullname => xxx, nickname => xxx)))
	 * becomes
	 * array(person.basic.full_name, person.basic.nickname)
	 * @var array
	 */
	protected $got_fields = array();

	/**
	 * @param array $input_data This is the 'data' item of the incoming request
	 */
	public function __construct(array $input_data = array())
	{
		$this->input_data = $input_data;
	}

	protected function _scanInputFieldNames(array $input, $key_parts = array())
	{
		foreach ($input as $k => $v) {
			$key_parts[] = $k;
			if (is_array($v)) {
				$this->_scanInputFieldNames($v, $key_parts);
			} else {
				$this->got_fields[] = implode('.', $key_parts);
			}
			array_pop($key_parts);
		}
	}


	
	/**
	 * Apply input to a form
	 * 
	 * @param \Orb\Form\Field\FieldGroup $form
	 */
	public function applyToForm(\Orb\Form\Field\FieldGroup $form)
	{
		$form->setFormData($this->input_data);
	}
}