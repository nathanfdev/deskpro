<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Form;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * The new participant form
 */
class NewTicketParticipantType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('first_name', 'text');
		$builder->add('last_name', 'text');
		$builder->add('email', 'text');
	}

	public function getName()
	{
		return 'newparticipant';
	}
}