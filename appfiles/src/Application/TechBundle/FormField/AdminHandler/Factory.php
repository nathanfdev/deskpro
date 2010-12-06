<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage TechBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\TechBundle\CustomField\AdminHandler;

use \Application\CoreBundle\Entity\FormField;

/**
 * Creates an admin handler based off of a particular form field.
 * 
 * @static
 */
class Factory
{
	private function __construct() { /* static */ }

	/**
	 * @return Application\TechBundle\CustomField\AdminHandler\AbstractAdminHandler
	 */
	public static function createFromFormField(FormField $form_field)
	{
		$handler = null;
		switch ($form_field['handler_class']) {
			case 'Application\\DeskPRO\\Form\\FieldHandler\\Text':
				$handler = new \Application\TechBundle\CustomField\AdminHandler\Text($form_field);
				break;

			case 'Application\\DeskPRO\\Form\\FieldHandler\\Textarea':
				$handler = new \Application\TechBundle\CustomField\AdminHandler\Textarea($form_field);
				break;

			case 'Application\\DeskPRO\\Form\\FieldHandler\\Choice':
				$handler = new \Application\TechBundle\CustomField\AdminHandler\Choice($form_field);
				break;

			case 'Application\\DeskPRO\\Form\\FieldHandler\\MultipleChoice':
				$handler = new \Application\TechBundle\CustomField\AdminHandler\MultipleChoice($form_field);
				break;
		}
		
		if (!$handler) {
			throw new \InvalidArgumentException("Unknown AdminHandler for {$form_field['handler_class']}");
		}

		return $handler;
	}
}