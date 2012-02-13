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

class EditEmailGateway extends AbstractType
{
	public function __construct()
	{

	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('connection_type', 'text');
		$builder->add('gateway_type', 'text');
		$builder->add('is_enabled', 'checkbox', array('required' => false));
		$builder->add('define_transport', 'checkbox', array('required' => false));
		$builder->add('address', 'text', array('required' => true));

		$options_form = $builder->create('pop3_options', 'form');
		$options_form->add('host', 'text', array('required' => false));
		$options_form->add('username', 'text', array('required' => false));
		$options_form->add('password', 'text', array('required' => false));
		$options_form->add('port', 'text', array('required' => false));
		$options_form->add('secure', 'choice', array('required' => false, 'empty_value' => '', 'choices' => array('ssl' => 'SSL', 'tls' => 'TLS')));
		$builder->add($options_form);

		$options_form = $builder->create('gmail_options', 'form');
		$options_form->add('username', 'text', array('required' => false));
		$options_form->add('password', 'text', array('required' => false));
		$builder->add($options_form);
	}

	public function getName()
	{
		return 'gateway';
	}
}
