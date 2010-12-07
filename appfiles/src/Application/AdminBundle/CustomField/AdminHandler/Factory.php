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

use Application\DeskPRO\Entity\CustomDefAbstract;

use Application\DeskPRO\App;

use Orb\Util\Util;

/**
 * Creates an admin handler based off of a particular form field.
 * 
 * @static
 */
class Factory
{
	private function __construct() { /* static */ }

	/**
	 * @return Application\AdminBundle\CustomField\AdminHandler\AbstractAdminHandler
	 */
	public static function createFromFormField(CustomDefAbstract $form_field)
	{
		$handler = null;

		$base_classname = Util::getBaseClassname($form_field['handler_class']);

		switch ($base_classname) {
			case 'Text':
				$handler = new \Application\AdminBundle\CustomField\AdminHandler\Text($form_field);
				break;

			case 'Textarea':
				$handler = new \Application\AdminBundle\CustomField\AdminHandler\Textarea($form_field);
				break;

			case 'Choice':
				$handler = new \Application\AdminBundle\CustomField\AdminHandler\Choice($form_field);
				break;

			case 'MultipleChoice':
				$handler = new \Application\AdminBundle\CustomField\AdminHandler\MultipleChoice($form_field);
				break;
		}
		
		if (!$handler) {
			throw new \InvalidArgumentException("Unknown AdminHandler for {$form_field['handler_class']}");
		}

		return $handler;
	}
}