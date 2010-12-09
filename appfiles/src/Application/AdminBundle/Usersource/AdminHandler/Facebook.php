<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Usersources
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Usersource\AdminHandler;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Setup for the facebook usersource
 */
class Facebook extends AbstractAdminHandler
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
			'name' => 'app_id',
			'attributes' => array('size' => 50)
		));
		$fields[] = $f;

		$f = new \Orb\Form\Field\Text(array(
			'name' => 'app_secret',
			'attributes' => array('size' => 50)
		));
		$fields[] = $f;

		return $fields;
	}
}