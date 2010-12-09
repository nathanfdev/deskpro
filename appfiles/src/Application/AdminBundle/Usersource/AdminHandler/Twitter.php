<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Usersources
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\AgentBundle\Usersource\AdminHandler;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * The setup classes handle showing the user a wizard, and then taking input and
 * transforming it (if necessary) into adapter options.
 */
class Twitter extends AbstractAdminHandler
{
	/**
	 * Return an array of fields we need to add to the form.
	 *
	 * @return array
	 */
	protected function buildFormFields()
	{
		$fields = array();

		$f = new \Orb\Form\Field\Text(array(
			'name' => 'consumer_key',
			'attributes' => array('size' => 50)
		));
		$fields[] = $f;

		$f = new \Orb\Form\Field\Text(array(
			'name' => 'consumer_secret',
			'attributes' => array('size' => 50)
		));
		$fields[] = $f;

		return $fields;
	}
}