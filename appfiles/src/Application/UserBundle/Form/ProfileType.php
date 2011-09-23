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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * The new ticket form
 */
class ProfileType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('first_name', 'text');
		$builder->add('last_name', 'text');
		$builder->add('timezone', 'choice', array(
			'choices' => array_combine(array_values(\DateTimeZone::listIdentifiers()), array_values(\DateTimeZone::listIdentifiers()))
		));
	}

	public function getName()
	{
		return 'profile';
	}
}
