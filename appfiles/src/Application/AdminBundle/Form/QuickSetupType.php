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

namespace Application\AdminBundle\Form;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class QuickSetupType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('default_from_email', 'text');

		$builder->add('site_url', 'text', array('required' => false));
		$builder->add('site_name', 'text', array('required' => false));
		$builder->add('deskpro_name', 'text', array('required' => false));
		$builder->add('deskpro_url', 'text');

		$builder->add('timezone', 'choice', array(
			'empty_value' => '',
			'choices' => array_combine(\DateTimeZone::listIdentifiers(), \DateTimeZone::listIdentifiers())
		));

		$builder->add('portal_enabled', 'checkbox', array('required' => false));
	}

	public function getName()
	{
		return 'setup';
	}
}
