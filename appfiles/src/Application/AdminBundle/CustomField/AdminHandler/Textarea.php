<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\AdminBundle\CustomField\AdminHandler;

/**
 * Handles editing and creating text field definitions
 */
class Textarea extends Text
{
	/**
	 * Return an array of fields we need to add to the form.
	 *
	 * @return array
	 */
	protected function buildRequiredFormFields()
	{
		$fields = array();

		$f = new \Orb\Form\Field\Text(array(
			'name' => 'min_length',
			'attributes' => array('length' => 4)
		));
		$fields[] = $f;

		$f = new \Orb\Form\Field\Text(array(
			'name' => 'max_length',
			'attributes' => array('length' => 4)
		));
		$fields[] = $f;

		return $fields;
	}
}