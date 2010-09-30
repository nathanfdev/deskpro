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

namespace Application\TechBundle\FormField\AdminHandler;

use \Application\CoreBundle\Entity\FormField;

/**
 * Creates an admin handler based off of a particular form field.
 * 
 * @static
 */
class Factor
{
	private function __construct() { /* static */ }

	/**
	 * @return Application\TechBundle\FormField\AdminHandler\AbstractAdminHandler
	 */
	public static function createFromFormField(FormField $form_field)
	{
		switch ($form_field['typeclass']) {
			case 'DeskPRO\\FormField\\Text':
				$handler = new \Application\TechBundle\FormField\AdminHandler\Text($form_field);
				break;

			case 'DeskPRO\\FormField\\Textarea':
				$handler = new \Application\TechBundle\FormField\AdminHandler\Textarea($form_field);
				break;

			case 'DeskPRO\\FormField\\Choice':
				$handler = new \Application\TechBundle\FormField\AdminHandler\Choice($form_field);
				break;
		}
	}
}