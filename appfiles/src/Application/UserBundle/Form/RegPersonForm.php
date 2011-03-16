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

class RegPersonForm extends \Symfony\Component\Form\Form
{
	protected function configure()
	{
		$this->setDataClass('Application\\UserBundle\\RegPerson');

		$this->addRequiredOption('custom_fields');

		$this->add(new Form\PasswordField('password'));
		$this->add(new Form\PasswordField('password2', array('property_path' => null)));

		$custom_fields = $this->getOption('custom_fields');
		$custom_fields->setKey('person_custom_fields');
		$custom_fields->setOption('property_path', 'person_custom_values');
		$this->add($custom_fields);
	}
}