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

namespace DeskPRO\Form;

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
	 * @param array $input_data This is the 'data' item of the incoming request
	 */
	public function __construct(array $input_data = array())
	{
		$this->input_data = $input_data;
	}


	
	/**
	 * Apply input to a form
	 * 
	 * @param \Orb\Form\Field\FieldGroup $form
	 */
	public function applyToForm(\Orb\Form\Field\FieldGroup $form)
	{
		$data = array();

		foreach ($this->input_data as $edit) {
			foreach ($edit as $k=>$v) {
				$data[$k] = $v;
			}
		}

		$form->setFormData($data);
	}



	/**
	 * Get the ID of a form group that we'll need to map rendered values back to.
	 * 
	 * @param string $name
	 * @return string
	 */
	public function getIdForField($name)
	{
		$id = null;

		foreach ($this->input_data as $input_id => $data) {
			if (isset($data[$name])) {
				$id = $input_id;
				break;
			}
		}

		return $id;
	}



	/**
	 * Get the names of fields. $base is the base name to get. So null means
	 * all top-level (group names). If you have a group and want to see which of the
	 * group were requested, then pass $base of the groupname and all child keys are
	 * returned etc.
	 *
	 * You could use this to lazy-load only form fields that need to be loaded.
	 *
	 * @param string $base
	 * @return array
	 */
	public function getFieldNames($base = null)
	{
		$names = array();

		foreach ($this->input_data as $data) {
			if ($base === null) {
				$names = array_merge($names, array_keys($data));
			} else {
				$data = Arrays::getValue($base);
				if (is_array($data)) {
					$names = array_merge($names, array_keys($data));
				}
			}
		}

		return $names;
	}
}