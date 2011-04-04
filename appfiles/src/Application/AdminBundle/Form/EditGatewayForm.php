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

class EditGatewayForm extends \Symfony\Component\Form\Form
{
	protected function configure()
	{
		$this->addRequiredOption('gateway');

		$this->add(new Form\TextField('name'));
		$this->add(new Form\TextField('address'));

		$form = new Form\Form('connection_options');
		$this->add($form);
		$form->add(new Form\TextField('server'));
		$form->add(new Form\TextField('username'));
		$form->add(new Form\TextField('password'));
		$form->add(new Form\TextField('port'));
		$form->add(new Form\CheckboxField('ssl', array('required' => false)));
	}
}