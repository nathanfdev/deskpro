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

use \Symfony\Component\Form;

class EditEmailFromForm extends \Symfony\Component\Form\Form
{
	protected function configure()
	{
		$this->addRequiredOption('email_from');

		$this->add(new Form\TextField('name'));
		$this->add(new Form\TextField('address'));

		$email_from = $this->getOption('email_from');

		$opt_forms = new Form\Form('transport_options');
		$opt_forms->add(new Form\HiddenField('type', array('data' => $email_from['transport_options']['type'])));

		switch ($email_from['transport_options']['type']) {
			case 'smtp':
				$opt_forms->add(new Form\TextField('server'));
				$opt_forms->add(new Form\TextField('port'));

				$ssl = new Form\ChoiceField('ssl', array(
					'required' => false,
					'choices' => array('' => 'Non-secure', 'ssl' => 'SSL', 'tls' => 'TLS')
				));
				$opt_forms->add($ssl);

				$opt_forms->add(new Form\TextField('username', array('required' => false)));
				$opt_forms->add(new Form\TextField('password', array('required' => false)));
				break;

			case 'sendmail':
				$opt_forms->add(new Form\TextField('sendmail_path'));
				break;
		}

		$this->add($opt_forms);
	}
}