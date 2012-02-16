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

class EditEmailTransport extends AbstractType
{
	public function __construct()
	{

	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('match_type', 'text');
		$builder->add('match_email', 'text', array('required' => false));
		$builder->add('match_domain', 'text', array('required' => false));
		$builder->add('match_regex', 'text', array('required' => false));

		$builder->add('transport_type', 'text');
		$builder->add('backup_transport_type', 'text', array('required' => false));

		foreach (array('smtp_options', 'backup_smtp_options') as $n) {
			$options_form = $builder->create($n, 'form');
			$options_form->add('host', 'text', array('required' => false));
			$options_form->add('username', 'text', array('required' => false));
			$options_form->add('password', 'text', array('required' => false));
			$options_form->add('port', 'text', array('required' => false));
			$options_form->add('secure', 'choice', array('required' => false, 'empty_value' => '', 'choices' => array('ssl' => 'SSL', 'tls' => 'TLS')));
			$builder->add($options_form);
		}

		foreach (array('gmail_options', 'backup_gmail_options') as $n) {
			$options_form = $builder->create($n, 'form');
			$options_form->add('username', 'text', array('required' => false));
			$options_form->add('password', 'text', array('required' => false));
			$builder->add($options_form);
		}
	}

	public function getName()
	{
		return 'transport';
	}
}
