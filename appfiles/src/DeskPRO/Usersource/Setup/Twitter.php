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

namespace DeskPRO\Usersource\Setup;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * The setup classes handle showing the user a wizard, and then taking input and
 * transforming it (if necessary) into adapter options.
 */
class Twitter extends AbstractSetup
{
	public function getAdapterClass()
	{
		return 'Orb\\Auth\\Adapter\\Twitter';
	}

	public function getAdapterOptions()
	{
		$options = array(
			'consumer_key' => $this->form_data['consumer_key'],
			'consumer_secret' => $this->form_data['consumer_secret']
		);

		return $options;
	}
}