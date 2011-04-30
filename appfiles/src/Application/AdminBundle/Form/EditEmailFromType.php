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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EditEmailFromType extends AbstractType
{
	protected $email_from;

	public function __construct($email_from)
	{
		$this->email_from = $email_from;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('name', 'text');
		$builder->add('address', 'text');
		
		$email_from = $this->email_from;

		$opt_forms = $builder->create('transport_options', 'form');

		$opt_forms->add('type', 'hidde', array('value' => $email_from['transport_options']['type']));

		switch ($email_from['transport_options']['type']) {
			case 'smtp':
				$opt_forms->add('server', 'text');
				$opt_forms->add('port', 'text');

				$opt_forms->add('ssl', 'choice', array(
					'required' => false,
					'choices' => array('' => 'Non-secure', 'ssl' => 'SSL', 'tls' => 'TLS')
				));

				$opt_forms->add('username', 'text', array('required' => false));
				$opt_forms->add('password', 'text', array('required' => false));
				break;

			case 'sendmail':
				$opt_forms->add('sendmail_path', 'text', array('required' => false));
				break;
		}

		$builder->add($opt_forms);
	}
}