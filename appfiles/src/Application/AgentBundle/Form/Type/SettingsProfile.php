<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class SettingsProfile extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
    {
		$builder->add('name', 'text');
		$builder->add('email', 'text');
		$builder->add('timezone', 'choice', array(
			'choices' => array_combine(\DateTimeZone::listIdentifiers(), \DateTimeZone::listIdentifiers())
		));
		$builder->add('password', 'password', array('required' => false));
		$builder->add('password2', 'password', array('required' => false));
		$builder->add('ticket_signature', 'textarea', array('required' => false));
    }

	public function getDefaultOptions(array $options)
	{
		return array(
			'data_class' => 'Application\\AgentBundle\\Form\\Model\\SettingsProfile',
		);
	}

    public function getName()
    {
        return 'settings_profile';
    }
}
