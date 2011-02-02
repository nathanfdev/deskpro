<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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

		// The handler classname should be the same basename, but different namespace
		$handler_classname = 'Application\\AdminBundle\\CustomField\\AdminHandler\\' . $base_classname;

		if (class_exists($handler_classname)) {
			$handler = new $handler_classname($form_field);
		}

		if (!$handler) {
			throw new \InvalidArgumentException("Unknown AdminHandler for {$form_field['handler_class']}");
		}

		return $handler;
	}
}