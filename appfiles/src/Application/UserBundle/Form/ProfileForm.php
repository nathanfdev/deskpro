<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Form;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use \Symfony\Component\Form;

/**
 * The new ticket form
 */
class ProfileForm extends \Symfony\Component\Form\Form
{
	protected function configure()
	{
		$this->add(new Form\TextField('first_name'));
		$this->add(new Form\TextField('last_name'));

		$this->add(new Form\ChoiceField('timezone', array(
			'choices' => array_combine(array_values(\DateTimeZone::listIdentifiers()), array_values(\DateTimeZone::listIdentifiers()))
		)));
	}

	public function validate()
	{
		return true;
	}
}