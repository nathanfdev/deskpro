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

class TicketAutoCloseOptionsType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('resolve_agent_reply', 'text');
		$builder->add('close_agent_reply', 'text');
		$builder->add('resolve_user_reply', 'text');
		$builder->add('close_user_reply', 'text');
	}
}
