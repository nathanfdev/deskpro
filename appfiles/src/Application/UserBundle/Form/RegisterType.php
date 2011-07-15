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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class RegisterType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$this->buildPersonForm($builder);
	}



	/**
	 * Configures the person form
	 */
	protected function buildPersonForm(FormBuilder $builder)
	{
		$person_builder = $builder->create('person', 'form')
			->add('name', 'text', array('required' => false));

		$person_builder->add('email', 'text');
		$person_builder->add('password', 'password');
		$person_builder->add('password2', 'password');

		$builder->add($person_builder);
	}

	public function getName()
	{
		return 'register';
	}
}