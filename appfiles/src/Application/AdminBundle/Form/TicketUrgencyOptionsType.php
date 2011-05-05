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

class TicketUrgencyOptionsType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('base_urgency', 'text');

		$time_options_builder = $builder->create('time_options', 'form');
		foreach (array('user_waiting', 'user_reply', 'open') as $key) {
			$time_options_builder->add($key, 'text');
			$time_options_builder->add($key.'_num', 'text');
		}
		$builder->add($time_options_builder);

		$user_options_builder = $builder->create('user_options', 'form');
		foreach (range(1,5) as $key) {
			$user_options_builder->add('importance_num_' . $key, 'text');
		}
		$builder->add($user_options_builder);
	}
}