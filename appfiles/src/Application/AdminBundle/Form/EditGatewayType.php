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

class EditGatewayType extends AbstractType
{
	protected $gateway;

	public function __construct($gateway)
	{
		$this->gateway = $gateway;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('name', 'text');
		$builder->add('address', 'text');

		$options_form = $builder->create('connection_options', 'form');
		$options_form->add('server', 'text');
		$options_form->add('username', 'text');
		$options_form->add('password', 'text', array('required' => false));
		$options_form->add('port', 'text', array('required' => false));
		$options_form->add('ssl', 'checkbox', array('required' => false));
		$builder->add($options_form);
	}
}