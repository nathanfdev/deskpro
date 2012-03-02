<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 */

namespace Application\AgentBundle\CustomField\AdminHandler;

use Application\DeskPRO\Entity\FormField;

/**
 * Creates an admin handler based off of a particular form field.
 *
 * @static
 */
class Factory
{
	private function __construct() { /* static */ }

	/**
	 * @return Application\AgentBundle\CustomField\AdminHandler\AbstractAdminHandler
	 */
	public static function createFromFormField(FormField $form_field)
	{
		$handler = null;
		switch ($form_field['handler_class']) {
			case 'Application\\DeskPRO\\Form\\FieldHandler\\Text':
				$handler = new \Application\AgentBundle\CustomField\AdminHandler\Text($form_field);
				break;

			case 'Application\\DeskPRO\\Form\\FieldHandler\\Textarea':
				$handler = new \Application\AgentBundle\CustomField\AdminHandler\Textarea($form_field);
				break;

			case 'Application\\DeskPRO\\Form\\FieldHandler\\Choice':
				$handler = new \Application\AgentBundle\CustomField\AdminHandler\Choice($form_field);
				break;

			case 'Application\\DeskPRO\\Form\\FieldHandler\\MultipleChoice':
				$handler = new \Application\AgentBundle\CustomField\AdminHandler\MultipleChoice($form_field);
				break;
		}

		if (!$handler) {
			throw new \InvalidArgumentException("Unknown AdminHandler for {$form_field['handler_class']}");
		}

		return $handler;
	}
}
