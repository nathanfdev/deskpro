<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */

namespace Application\AdminBundle\Form;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Symfony\Component\Form;

class EditTwitterAccountForm extends \Symfony\Component\Form\Form
{
	protected function configure()
	{
		$this->addRequiredOption('account');

		$this->add(new Form\TextField('oauth_token'));
		$this->add(new Form\TextField('oauth_token_secret'));
	}
}
