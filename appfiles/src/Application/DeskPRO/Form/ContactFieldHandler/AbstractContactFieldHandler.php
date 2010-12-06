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

namespace Application\DeskPRO\Form\ContactFieldHandler;

use \Orb\Util\Strings;

use \Application\DeskPRO\Entity;

/**
 * A ContactFieldHandler handles processing of contact fields like address, phone etc.
 */
abstract class AbstractContactFieldHandler implements \Orb\Form\Transformer\TransformerInterface, \Orb\Validator\ValidatorInterface
{
	/**
	 * A map of somefield => field_x for storage
	 * @var array
	 */
	protected $name_to_field = array();
	
	/**
	 * Reverse of above
	 */
	protected $field_to_name = array();


	public function __construct()
	{
		$this->field_to_name = array_flip($this->name_to_field);
	}

	/**
	 * Convert a "simple name" (ie used in forms) to the corresponding
	 * handler class.
	 * 
	 * @param string $simple_name
	 * @return string
	 */
	public static function simpleNameToClassName($simple_name)
	{
		$simple_name = str_replace('_', '-', $simple_name);
		$classname = Strings::dashToCamelCase($simple_name);
		$classname = ucfirst($classname);
		$classname = "Application\\DeskPRO\\Form\\ContactFieldHandler\\$classname";

		return $classname;
	}



	/**
	 * Transforms stored data to data the form controls can use
	 *
	 * @param  mixed $value     The stored data
	 * @return mixed
	 */
	public function transformStoredToForm($value)
	{
		if (!$value) {
			return null;
		}

		$ret_value = array('comment' => $value['comment']);

		foreach ($this->field_to_name as $field => $name) {
			$ret_value[$name] = !empty($value[$field]) ? $value[$field] : '';
		}

		return $ret_value;
	}



	/**
	 * Transforms form data into data we can store.
	 *
	 * @param  mixed $value     The form data
	 * @return mixed            The data we can store
	 */
	public function transformFormToStored($value)
	{
		if (!$value) {
			return null;
		}
		
		$ret_value = array('comment' => $value['comment']);

		foreach ($this->name_to_field as $name => $field) {
			$ret_value[$field] = !empty($value[$name]) ? $value[$name] : '';
		}

		return $ret_value;
	}

	

	/**
	 * The Form will call this with an array of values from the form. Check if its
	 * valid.
	 *
	 * @param array $value
	 */
	public function isValid($value)
	{
		return true;
	}



	/**
	 * If isValid returns false, this needs to be an array of errors.
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return array();
	}



	
	/**
	 * Get the Orb\Form\Field object for this type.
	 */
	abstract public function getFormField();

	

	public function getSimpleName()
	{
		// Application\DeskPRO\Form\ContactFieldHandler\Address becomes address
		// Application\DeskPRO\Form\ContactFieldHandler\Address becomes address
		// -> Good for form names etc

		$classname = get_class($this);
		$classname = explode('\\', $classname);
		$classname = array_pop($classname);
		$classname = Strings::camelCaseToDash($classname);
		$classname = str_replace('-', '_', $classname);

		return $classname;
	}


	public function mapNameToField($name)
	{
		return $this->name_to_field[$name];
	}

	public function mapFieldToName($field)
	{
		return $this->field_to_name[$field];
	}
}